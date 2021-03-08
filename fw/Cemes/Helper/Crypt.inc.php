<?php
if (!$GLOBALS["CEMES"]["ACTIVE"])
    die("Framework ist nicht aktiv");
/*
 * Singleton-Klasse
 *
 * @package Cemes-Framework
 * @version 1.0.0
 * @author Tim Werdin
 *
 * Die Klasse soll Datenvalidieren
 * TODO neuschreiben
 */
class Cemes_Helper_Crypt extends Cemes_Pattern_Singleton {
    // instance: die Instanz der Klasse
    protected static $instance = null;

    public function encrypt($mode, $data, $option = NULL) {
        switch(strtolower($mode)) {
            case "md5":
                $this->encrypt_md5($data, $option);
                break;
            case "sha1":
                $this->encrypt_sha1($data, $option);
                break;
            case "aes":
                $this->encrypt_aes($data, $option);
                break;
        }
    }
    public function decrypt($mode, $data, $password, $iv) {
        switch(strtolower($mode)) {
            case "aes":
                $this->decrypt_aes($data, $password, $iv);
                break;
        }
    }
    private function encrypt_md5($data, $salt) {
        if($salt == NULL || $salt = "")
            return md5($data);
        else
            return md5($data.$salt);
    }
    private function encrypt_sha1($data, $salt) {
        if($salt == NULL || $salt = "")
            return sha1($data);
        else
            return sha1($data.$salt);
    }
    private function encrypt_aes($data, $options) {
        if($options['password'] == NULL || $options['password'] == "")
            return false;
        else {
            // Setzt den Algorithmus
            switch ($options['aes']) {
                case 128:
                    $rijndael = 'rijndael-128';
                    break;
                case 192:
                    $rijndael = 'rijndael-192';
                    break;
                case 256:
                default:
                    $rijndael = 'rijndael-256';
            }
            // Setzt den Verschlüsselungsalgorithmus
            // und setzt den Output Feedback (OFB) Modus
            $cp = mcrypt_module_open($rijndael, '', 'ofb', '');
            // Ermittelt den Initialisierungsvector, der für die Modi CBC, CFB und OFB benötigt wird.
            // Der Initialisierungsvector muss beim Entschlüsseln den selben Wert wie beim Verschlüsseln haben.
            // Windows unterstützt nur MCRYPT_RAND
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
                $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($cp), MCRYPT_RAND);
            else
                $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($cp), MCRYPT_DEV_RANDOM);
            // Ermittelt die Anzahl der Bits, welche die Schlüssellänge des Keys festlegen
            $ks = mcrypt_enc_get_key_size($cp);
            // Erstellt den Schlüssel, der für die Verschlüsselung genutzt wird
            $key = substr(md5($options['password']), 0, $ks);
            // Initialisiert die Verschlüsselung
            mcrypt_generic_init($cp, $key, $iv);
            // Verschlüsselt die Daten
            $data["encrypted"] = mcrypt_generic($cp, $data);
            $data["iv"] = $iv;
            // Deinitialisiert die Verschlüsselung
            mcrypt_generic_deinit($cp);
            // Schließt das Modul
            mcrypt_module_close($cp);
            return $data;
        }
    }
    private function decrypt_aes($data, $password, $iv, $aes = 256)
    {
        // Setzt den Algorithmus
        switch ($aes) {
            case 128:
                $rijndael = 'rijndael-128';
                break;
            case 192:
                $rijndael = 'rijndael-192';
                break;
            case 256:
            default:
                $rijndael = 'rijndael-256';
        }
        // Setzt den Verschlüsselungsalgorithmus
        // und setzt den Output Feedback (OFB) Modus
        $cp = mcrypt_module_open($rijndael, '', 'ofb', '');
        // Ermittelt die Anzahl der Bits, welche die Schlüssellänge des Keys festlegen
        $ks = mcrypt_enc_get_key_size($cp);
        // Erstellt den Schlüssel, der für die Verschlüsselung genutzt wird
        $key = substr(md5($password), 0, $ks);
        // Initialisiert die Verschlüsselung
        mcrypt_generic_init($cp, $key, $iv);
        // Entschlüsselt die Daten
        $decrypted = mdecrypt_generic($cp, $data);
        // Beendet die Verschlüsselung
        mcrypt_generic_deinit($cp);
        // Schließt das Modul
        mcrypt_module_close($cp);
        return trim($decrypted);
    }
}