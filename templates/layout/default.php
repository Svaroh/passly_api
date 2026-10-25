<?php
/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SA (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SA (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         2.0.0
 */
use Cake\Core\Configure;
?>
<!doctype html>
<html class="passbolt no-js version launching no-passboltplugin" lang="en">
<head>
    <?= $this->Html->charset() ?>

    <title><?= Configure::read('passbolt.meta.title'); ?> | <?= $this->fetch('title') ?></title>
    <?= $this->element('Header/meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('scriptTop') ?>
</head>
<body spellcheck="false">
<!-- main -->
<div id="container" class="page <?php echo $this->fetch('page_classes') ?>">
<?php echo $this->fetch('content'); ?>
</div>
<?php echo $this->fetch('scriptBottom'); ?>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/service-worker.js', { scope: '/' })
            .catch(function(err) {
                console.debug('ServiceWorker registration error: ', err);
            });
    });
}
</script>
</body>
</html>
