<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/files')]
class FileManagerController extends AbstractController
{
    public function __construct(
        private SluggerInterface $slugger
    ) {}

    #[Route('/', name: 'admin_files_index')]
    public function index(): Response
    {
        // Lister les fichiers existants
        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
        $files = [];
        
        if (is_dir($uploadsDir)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($uploadsDir)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $relativePath = str_replace($uploadsDir, '', $file->getPathname());
                    $relativePath = str_replace('\\', '/', $relativePath);
                    $files[] = [
                        'name' => $file->getFilename(),
                        'path' => '/uploads' . $relativePath,
                        'size' => $file->getSize(),
                        'modified' => $file->getMTime(),
                        'type' => $this->getFileType($file->getExtension())
                    ];
                }
            }
        }

        return $this->render('admin/files/index.html.twig', [
            'files' => $files,
        ]);
    }

    #[Route('/upload', name: 'admin_files_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $uploadedFile = $request->files->get('file');
        $category = $request->request->get('category', 'general');

        if (!$uploadedFile) {
            return new JsonResponse(['error' => 'Aucun fichier sélectionné'], 400);
        }

        // Validation du fichier
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        if (!in_array($uploadedFile->getMimeType(), $allowedTypes)) {
            return new JsonResponse(['error' => 'Type de fichier non autorisé'], 400);
        }

        // Limite de taille (5MB)
        if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
            return new JsonResponse(['error' => 'Fichier trop volumineux (max 5MB)'], 400);
        }

        try {
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

            // Créer le dossier de destination
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $category;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uploadedFile->move($uploadDir, $newFilename);

            return new JsonResponse([
                'success' => true,
                'filename' => $newFilename,
                'path' => '/uploads/' . $category . '/' . $newFilename,
                'url' => $request->getSchemeAndHttpHost() . '/uploads/' . $category . '/' . $newFilename
            ]);

        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Erreur lors de l\'upload: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/delete', name: 'admin_files_delete', methods: ['POST'])]
    public function delete(Request $request): JsonResponse
    {
        $filePath = $request->request->get('path');
        
        if (!$filePath) {
            return new JsonResponse(['error' => 'Chemin de fichier manquant'], 400);
        }

        $fullPath = $this->getParameter('kernel.project_dir') . '/public' . $filePath;
        
        if (!file_exists($fullPath)) {
            return new JsonResponse(['error' => 'Fichier non trouvé'], 404);
        }

        if (unlink($fullPath)) {
            return new JsonResponse(['success' => true]);
        } else {
            return new JsonResponse(['error' => 'Impossible de supprimer le fichier'], 500);
        }
    }

    #[Route('/browse', name: 'admin_files_browse')]
    public function browse(Request $request): Response
    {
        $category = $request->query->get('category', 'general');
        $callback = $request->query->get('callback', '');

        return $this->render('admin/files/browse.html.twig', [
            'category' => $category,
            'callback' => $callback,
        ]);
    }

    #[Route('/api/list', name: 'admin_files_api_list', methods: ['GET'])]
    public function apiList(Request $request): JsonResponse
    {
        $category = $request->query->get('category', 'general');
        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $category;
        $files = [];
        
        if (is_dir($uploadsDir)) {
            $iterator = new \DirectoryIterator($uploadsDir);
            
            foreach ($iterator as $file) {
                if ($file->isFile() && !$file->isDot()) {
                    $files[] = [
                        'name' => $file->getFilename(),
                        'path' => '/uploads/' . $category . '/' . $file->getFilename(),
                        'size' => $file->getSize(),
                        'modified' => $file->getMTime(),
                        'type' => $this->getFileType($file->getExtension())
                    ];
                }
            }
        }

        // Trier par date de modification (plus récent en premier)
        usort($files, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        return new JsonResponse(['files' => $files]);
    }

    private function getFileType(string $extension): string
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        
        if (in_array(strtolower($extension), $imageExtensions)) {
            return 'image';
        }
        
        return 'file';
    }
}