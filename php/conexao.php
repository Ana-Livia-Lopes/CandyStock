<?php

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "candystock_db"
);

if ($conn->errno) {
    die("Falha na conexao: " . $conn->error);
}