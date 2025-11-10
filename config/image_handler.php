<?php
/**
 * Gestionnaire d'images pour économiser la mémoire serveur
 * Règles :
 * - Max 5 MB par image
 * - Formats autorisés: jpg, jpeg, png, gif, webp
 * - Renommage avec hash pour éviter les collisions
 * - Suppression automatique des images orphelines
 */

class ImageHandler {
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB
    private const ALLOWED_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const UPLOAD_DIR = '../img_produit/';
    private const DEFAULT_IMAGE = 'default.png';

    /**
     * Valide et upload une image
     * @param array $file Le fichier du formulaire $_FILES
     * @return string|false Nom du fichier uploadé ou false
     */
    public static function upload($file) {
        // Pas de fichier uploadé = pas d'erreur
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return false;
        }

        // erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log("Erreur upload image: " . $file['error']);
            return false;
        }

        // taille max : MAX_FILE_SIZE (ici 5MB)
        if ($file['size'] > self::MAX_FILE_SIZE) {
            error_log("Image trop volumineuse: " . $file['size'] . " bytes");
            return false;
        }

        // Vérifier le type
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_TYPES)) {
            error_log("Format non autorisé: " . $ext);
            return false;
        }

        // Générer un nom unique avec timestamp et hash
        $newFileName = 'img_' . time() . '_' . substr(md5($file['name'] . microtime()), 0, 8) . '.' . $ext;
        // substr limite la taille du hash pour éviter des noms trop longs (ici 8 caractères max)
        $targetPath = self::UPLOAD_DIR . $newFileName;

        // Créer le répertoire s'il n'existe pas
        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }

        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $newFileName;
        }

        error_log("Échec du déplacement du fichier: " . $file['name']);
        return false;
    }

    /**
     * Supprime une image si elle n'est pas l'image par défaut
     * @param string $fileName Nom du fichier
     * @return bool Succès de la suppression
     */
    public static function delete($fileName) {
        if (empty($fileName) || $fileName === self::DEFAULT_IMAGE) {
            return true; // Ne pas supprimer l'image par défaut
        }

        $filePath = self::UPLOAD_DIR . $fileName;
        if (file_exists($filePath) && is_file($filePath)) {
            return unlink($filePath);
        }

        return true; // Fichier inexistant = pas d'erreur
    }

    /**
     * Remplace une ancienne image par une nouvelle
     * Gère l'ancienne image automatiquement
     * @param string $oldFileName Nom du fichier ancien
     * @param array $newFile Nouveau fichier uploadé
     * @return string|false Nom du nouveau fichier ou false
     */
    public static function replace($oldFileName, $newFile) {
        $newFileName = self::upload($newFile);
        
        if ($newFileName) {
            self::delete($oldFileName); // Supprimer l'ancienne
            return $newFileName;
        }

        return false;
    }
}
?>
