<?php
/**
 * File: config/session.php
 * Fungsi: Inisialisasi session global
 * CATATAN: JANGAN ADA LOGIKA LAIN DI SINI
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
