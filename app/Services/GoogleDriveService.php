<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    private $clientId;
    private $clientSecret;
    private $refreshToken;
    private $folderId;
    private $accessToken;

    public function __construct()
    {
        // Leer directo del .env si config está cacheado con valores vacíos
        $this->clientId = config('services.google_drive.client_id') ?: env('GOOGLE_DRIVE_CLIENT_ID');
        $this->clientSecret = config('services.google_drive.client_secret') ?: env('GOOGLE_DRIVE_CLIENT_SECRET');
        $this->refreshToken = config('services.google_drive.refresh_token') ?: env('GOOGLE_DRIVE_REFRESH_TOKEN');
        $this->folderId = config('services.google_drive.folder_id') ?: env('GOOGLE_DRIVE_FOLDER_ID');
    }

    public function isConfigured(): bool
    {
        return !empty($this->clientId)
            && !empty($this->clientSecret)
            && !empty($this->refreshToken)
            && !empty($this->folderId);
    }

    private function getAccessToken()
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
            'grant_type' => 'refresh_token'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

        $response = curl_exec($ch);
        curl_close($ch);
      //  dd($response);
        $data = json_decode($response, true);

        if (isset($data['access_token'])) {
            $this->accessToken = $data['access_token'];
            return $this->accessToken;
        }

        Log::error('Error obteniendo access token Google Drive: ' . $response);
        return null;
    }

    public function createDocument($name, $content = '')
    {
        if (!$this->isConfigured()) {
            Log::warning('Google Drive no configurado');
            return null;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        $metadata = [
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.document',
            'parents' => [$this->folderId]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');
        curl_setopt($ch, CURLOPT_POST, true);

        $boundary = bin2hex(random_bytes(16));
        $body = "--$boundary\r\n";
        $body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $body .= json_encode($metadata) . "\r\n";
        $body .= "--$boundary\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $body .= $content . "\r\n";
        $body .= "--$boundary--";

        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken",
            "Content-Type: multipart/related; boundary=$boundary"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $file = json_decode($response, true);

        if ($httpCode === 200 || $httpCode === 201) {
            return $this->formatDocumentResponse($file);
        }

        Log::error('Error creando documento Google Drive: ' . $response);
        return null;
    }

    public function copyDocument($templateId, $newName)
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/drive/v3/files/$templateId/copy");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'name' => $newName,
            'parents' => [$this->folderId]
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $file = json_decode($response, true);

        if ($httpCode === 200 || $httpCode === 201) {
            return $this->formatDocumentResponse($file);
        }

        Log::error('Error copiando documento Google Drive: ' . $response);
        return null;
    }

    public function getDocumentLink($fileId)
    {
        return "https://docs.google.com/document/d/$fileId/edit";
    }

    public function shareDocument($fileId)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }

        $permission = [
            'type' => 'anyone',
            'role' => 'writer'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/drive/v3/files/$fileId/permissions");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($permission));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200 || $httpCode === 201;
    }

    private function formatDocumentResponse($file)
    {
        return [
            'id' => $file['id'],
            'name' => $file['name'],
            'link' => $this->getDocumentLink($file['id']),
            'webViewLink' => $file['webViewLink'] ?? $this->getDocumentLink($file['id'])
        ];
    }

    public function uploadFile($content, $fileName, $mimeType = 'application/pdf')
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        $metadata = [
            'name' => $fileName,
            'parents' => [$this->folderId]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');
        curl_setopt($ch, CURLOPT_POST, true);

        $boundary = bin2hex(random_bytes(16));
        $body = "--$boundary\r\n";
        $body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $body .= json_encode($metadata) . "\r\n";
        $body .= "--$boundary\r\n";
        $body .= "Content-Type: $mimeType\r\n\r\n";
        $body .= $content . "\r\n";
        $body .= "--$boundary--";

        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken",
            "Content-Type: multipart/related; boundary=$boundary"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $file = json_decode($response, true);

        if ($httpCode === 200 || $httpCode === 201) {
            $this->shareDocument($file['id']);
            return [
                'id' => $file['id'],
                'name' => $file['name'],
                'link' => "https://drive.google.com/file/d/{$file['id']}/view",
                'download_link' => "https://drive.google.com/uc?export=download&id={$file['id']}"
            ];
        }

        Log::error('Error subiendo archivo a Google Drive: ' . $response);
        return null;
    }

    public function getViewUrl($fileId)
    {
        return "https://drive.google.com/file/d/$fileId/view";
    }

    public function getDownloadUrl($fileId)
    {
        return "https://drive.google.com/uc?export=download&id=$fileId";
    }

    public function listDocuments($pageSize = 100)
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return [];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/drive/v3/files?pageSize=$pageSize&q='$this->folderId'+in+parents+and+trashed=false&fields=files(id,name,mimeType,createdTime,webViewLink)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        return $data['files'] ?? [];
    }

    /**
     * Buscar documento por nombre en la carpeta de Drive
     */
    public function buscarDocumentoPorNombre($nombre)
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        // Buscar archivo con el nombre exacto en la carpeta
        $query = urlencode("'$this->folderId' in parents and name='$nombre' and trashed=false");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/drive/v3/files?q=$query&fields=files(id,name,mimeType)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (!empty($data['files']) && count($data['files']) > 0) {
            return $data['files'][0];
        }

        return null;
    }
}
