<?php
// php/logout.php

session_start();


class LogoutController
{
    public function __construct()
    {
        $this->logout();
    }

    private function logout(): void
    {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
}

new LogoutController();
