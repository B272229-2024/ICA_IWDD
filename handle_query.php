<?php
session_start();
require_once 'db/db_connection.php';

$protein = $_POST['protein'];
$taxon = $_POST['taxon'];
$limit = $_POST['limit'] ?? 10;
$min_len = isset($_POST['use_length_filter']) ? (int)$_POST['min_len'] : 0;
$max_len = isset($_POST['use_length_filter']) ? (int)$_POST['max_len'] : 100000;

if ($min_len > $max_len) {
    die("Error: Minimum length cannot be greater than maximum length.");
}

$run_id = uniqid("run_");
$cmd = escapeshellcmd("python3 scripts/new_get_sequences.py \"$protein\" \"$taxon\" $limit $min_len $max_len $run_id");
$output = shell_exec($cmd);
echo "<h3>Python Output:</h3><pre>$output</pre>";

// Read CSV file
$csv_path = "scripts/output/$run_id/sequences.csv";
if (!file_exists($csv_path)) {
    die("Error: Sequence CSV not found.");
}

$csv_data = array_map('str_getcsv', file($csv_path));
array_shift($csv_data); // Remove header

// Insert query
$query_sql = "INSERT INTO Queries (search_id, protein_family, taxon, min_len, max_len, no_of_sequences)
              VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($query_sql);
$stmt->execute([$run_id, $protein, $taxon, $min_len, $max_len, count($csv_data)]);

// Insert sequences
$seq_sql = "INSERT INTO Sequences (refseq_id, search_id, species, sequence)
            VALUES (?, ?, ?, ?)";
$seq_stmt = $pdo->prepare($seq_sql);

foreach ($csv_data as $row) {
    [$refseq_id, $species, $sequence, $description] = $row;
    $seq_stmt->execute([$refseq_id, $run_id, $species, $sequence]);
}

// Show results
echo "<h3>Stored Sequences for Run: $run_id</h3>";
echo "<table border='1' cellpadding='6'>
<tr><th>RefSeq ID</th><th>Species</th><th>Length</th><th>Description</th></tr>";

foreach ($csv_data as $row) {
    [$refseq_id, $species, $sequence, $description] = $row;
    $length = strlen($sequence);
    echo "<tr>
        <td>$refseq_id</td>
        <td>$species</td>
        <td>$length</td>
        <td>$description</td>
    </tr>";
}

echo "</table>";
echo "<p><a href='scripts/output/$run_id/sequences.fasta' download>Download FASTA</a></p>";

// === Run ClustalO ===
$input_fasta = "scripts/output/$run_id/sequences.fasta";
$alignment_out = "scripts/output/$run_id/alignment.aln";
$distmat_out = "scripts/output/$run_id/identity_matrix.txt";
$tree_out = "scripts/output/$run_id/guide_tree.dnd";

$clustalo_cmd = escapeshellcmd("bash scripts/run_clustalo.sh \"$input_fasta\" \"$alignment_out\" \"$distmat_out\" \"$tree_out\"");
$clustalo_output = shell_exec($clustalo_cmd);

echo "<h3>ClustalO Output:</h3><pre>$clustalo_output</pre>";

// === Insert into Analyses table ===
$insert_analysis = $pdo->prepare("INSERT INTO Analyses (search_id, type, result_path, label, file_type) VALUES (?, ?, ?, ?, ?)");

// Helper to do it in one line
function add_analysis($pdo, $run_id, $type, $path, $label) {
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    global $insert_analysis;
    $insert_analysis->execute([$run_id, $type, $path, $label, $ext]);
}

// Save each result into the DB
if (file_exists($alignment_out)) {
    add_analysis($pdo, $run_id, 'clustalo', $alignment_out, 'ClustalO alignment file');
}
if (file_exists($distmat_out)) {
    add_analysis($pdo, $run_id, 'clustalo', $distmat_out, 'Sequence identity matrix');
}
if (file_exists($tree_out)) {
    add_analysis($pdo, $run_id, 'clustalo', $tree_out, 'ClustalO guide tree (Newick format)');
}

echo "<h3>Analysis Outputs</h3>";

$analysis_sql = "SELECT type, label, result_path, file_type, created_at FROM Analyses WHERE search_id = ?";
$stmt = $pdo->prepare($analysis_sql);
$stmt->execute([$run_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($rows) {
    echo "<table border='1' cellpadding='6'>
    <tr><th>Type</th><th>Description</th><th>Download</th><th>Created</th></tr>";
    foreach ($rows as $row) {
        $filename = basename($row['result_path']);
        echo "<tr>
            <td>{$row['type']}</td>
            <td>{$row['label']}</td>
            <td><a href='{$row['result_path']}' download='$filename'>Download</a></td>
            <td>{$row['created_at']}</td>
        </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No analysis files found.</p>";
}


echo "<a href='index.php'>Back to Homepage</a>"
?>