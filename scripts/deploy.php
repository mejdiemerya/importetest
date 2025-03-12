<?php
echo "🚀 Début du processus de déploiement Drupal sur Pantheon...\n";

// 1️⃣ Mise à jour de la base de données
echo "🔄 Exécution de drush updb...\n";
passthru('drush updb -y 2>&1', $return_code);
if ($return_code !== 0) {
    echo "❌ Erreur lors de l'exécution de drush updb.\n";
    exit(1);
}
echo "✅ drush updb terminé avec succès.\n";

// 2️⃣ Import de la configuration (si nécessaire)
echo "🔄 Exécution de drush cim...\n";
passthru('drush cim -y 2>&1', $return_code);
if ($return_code !== 0) {
    echo "❌ Erreur lors de l'exécution de drush cim.\n";
    exit(1);
}
echo "✅ drush cim terminé avec succès.\n";

// 3️⃣ Nettoyage des caches
echo "🔄 Exécution de drush cr...\n";
passthru('drush cr 2>&1', $return_code);
if ($return_code !== 0) {
    echo "❌ Erreur lors de l'exécution de drush cr.\n";
    exit(1);
}
echo "✅ drush cr terminé avec succès.\n";

echo "🚀 Déploiement terminé avec succès !\n";
?>
