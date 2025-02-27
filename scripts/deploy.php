<?php
// Script exécuté après un déploiement sur Pantheon
echo "🚀 Exécution des éléments de deploiement Drupal...\n";

// Exécute `composer install` dans le dossier webroot de Pantheon
passthru('composer install --optimize-autoloader 2>&1');
echo "✅ Composer install terminé.\n";
?>