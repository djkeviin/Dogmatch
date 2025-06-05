<?php

// Configurar el manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Crear el directorio de imágenes si no existe
$imgDir = __DIR__ . '/../public/img';
if (!file_exists($imgDir)) {
    if (!mkdir($imgDir, 0777, true)) {
        die("Error: No se pudo crear el directorio de imágenes\n");
    }
    echo "✓ Directorio de imágenes creado: $imgDir\n";
}

// Crear una imagen por defecto primero
$defaultImg = $imgDir . '/default-dog.jpg';
if (!file_exists($defaultImg)) {
    $defaultImgUrl = 'https://images.dog.ceo/breeds/retriever-golden/n02099601_1024.jpg';
    $contenido = @file_get_contents($defaultImgUrl);
    if ($contenido !== false) {
        file_put_contents($defaultImg, $contenido);
        echo "✓ Imagen por defecto creada\n";
    } else {
        // Si no se puede descargar, usar una URL de placeholder
        $placeholderUrl = 'https://placehold.co/400x400/e0e0e0/969696.jpg?text=No+image';
        $contenido = @file_get_contents($placeholderUrl);
        if ($contenido !== false) {
            file_put_contents($defaultImg, $contenido);
            echo "✓ Imagen por defecto creada (placeholder)\n";
        } else {
            echo "✗ No se pudo crear la imagen por defecto\n";
            exit(1);
        }
    }
}

// Array de URLs de imágenes de perros
$imagenes = [
    'labrador1.jpg' => 'https://images.dog.ceo/breeds/retriever-labrador/n02099712_1003.jpg',
    'husky1.jpg' => 'https://images.dog.ceo/breeds/husky/n02110185_1469.jpg',
    'pastor_aleman1.jpg' => 'https://images.dog.ceo/breeds/germanshepherd/n02106662_1012.jpg',
    'chihuahua1.jpg' => 'https://images.dog.ceo/breeds/chihuahua/n02085620_1205.jpg',
    'golden1.jpg' => 'https://images.dog.ceo/breeds/retriever-golden/n02099601_1380.jpg',
    'beagle1.jpg' => 'https://images.dog.ceo/breeds/beagle/n02088364_1025.jpg',
    'yorkshire1.jpg' => 'https://images.dog.ceo/breeds/terrier-yorkshire/n02094433_1020.jpg',
    'bulldog1.jpg' => 'https://images.dog.ceo/breeds/bulldog-french/n02108915_1025.jpg',
    'rottweiler1.jpg' => 'https://images.dog.ceo/breeds/rottweiler/n02106550_1020.jpg',
    'poodle1.jpg' => 'https://images.dog.ceo/breeds/poodle-standard/n02113799_1020.jpg'
];

// Descargar y guardar cada imagen
foreach ($imagenes as $nombreArchivo => $url) {
    $rutaDestino = $imgDir . '/' . $nombreArchivo;
    
    if (!file_exists($rutaDestino)) {
        echo "Descargando $nombreArchivo...\n";
        $contenido = @file_get_contents($url);
        
        if ($contenido !== false) {
            if (file_put_contents($rutaDestino, $contenido) !== false) {
                echo "✓ Imagen guardada: $nombreArchivo\n";
            } else {
                echo "✗ Error al guardar: $nombreArchivo\n";
                copy($defaultImg, $rutaDestino);
                echo "  → Usando imagen por defecto\n";
            }
        } else {
            echo "✗ Error al descargar: $nombreArchivo\n";
            copy($defaultImg, $rutaDestino);
            echo "  → Usando imagen por defecto\n";
        }
    } else {
        echo "→ La imagen ya existe: $nombreArchivo\n";
    }
}

echo "\n¡Proceso completado!\n"; 