<?php

include 'php/features.php';

if (Usuario::hasSessao()) {
    Usuario::getSessao()->sair();
}

header("Location: login.php");