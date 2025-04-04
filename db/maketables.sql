CREATE DATABASE IF NOT EXISTS s2756532_web_project ;
use s2756532_web_project ;

CREATE TABLE IF NOT EXISTS `Users` (
  `user_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- https://www.w3schools.com/mysql/mysql_datatypes.asp
-- Queries table: each query has a unique run ID (TEXT from PHP)
CREATE TABLE IF NOT EXISTS `Queries` (
  `search_id` VARCHAR(50) PRIMARY KEY NOT NULL,  -- PHP run_id
  `user_id` INT UNSIGNED NULL,
  `protein_family` VARCHAR(255) NOT NULL,
  `taxon` VARCHAR(255) NOT NULL,
  `min_len` INT UNSIGNED NOT NULL,
  `max_len` INT UNSIGNED NOT NULL,
  `no_of_sequences` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `Users`(`user_id`) ON DELETE CASCADE
);

-- https://www.geeksforgeeks.org/mysql-on-delete-cascade-constraint/
-- Sequences table: each sequence links to a query/search via search_id
CREATE TABLE IF NOT EXISTS `Sequences` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `refseq_id` VARCHAR(50) NOT NULL,  -- NCBI RefSeq ID (e.g., XP_123456.1)
  `search_id` VARCHAR(50) NOT NULL,
  `species` VARCHAR(255) NOT NULL,
  `sequence` TEXT NOT NULL,
  FOREIGN KEY (`search_id`) REFERENCES `Queries`(`search_id`) ON DELETE CASCADE
);

-- Motifs table: each motif hit links to a sequence and a query
CREATE TABLE IF NOT EXISTS `Motifs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `search_id` VARCHAR(50) NOT NULL,
  `sequence_id` INT NOT NULL,
  `prosite_id` VARCHAR(20) NOT NULL,     -- e.g. PS00123
  `motif_name` VARCHAR(255) NOT NULL,    -- e.g. PROTEIN_KINASE_ATP
  `start_pos` INT UNSIGNED NOT NULL,
  `end_pos` INT UNSIGNED NOT NULL,
  FOREIGN KEY (`search_id`) REFERENCES `Queries`(`search_id`) ON DELETE CASCADE,
  FOREIGN KEY (`sequence_id`) REFERENCES `Sequences`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `Analyses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `search_id` VARCHAR(50) NOT NULL,
  type ENUM('clustalo', 'plotcon', 'motif', 'identity_matrix' 'custom') NOT NULL,
  `result_path` TEXT,              -- path to output file (.aln, .png, etc.)
  `label` VARCHAR(255),               -- Human-readable filename
  `file_type` VARCHAR(32),            -- e.g. 'png', 'aln', 'txt'
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`search_id`) REFERENCES `Queries`(`search_id`) ON DELETE CASCADE
);
