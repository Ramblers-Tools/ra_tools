<?php
/**
 * @version    3.7.4
 * @package    com_ra_tools
 * @author     GitHub Copilot
 * @copyright  2026
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;
$toolsHelper = new ToolsHelper;

$wa = $this->document->getWebAssetManager();
$wa->registerAndUseStyle('ramblers', 'com_ra_tools/ramblers.css');
$wa->registerAndUseStyle('dashboard', 'com_ra_tools/dashboard.css');
?>
<div style="background-color: <?php echo $this->site->colour; ?>; padding: 10px; border-radius: 5px;">
    <h2><?php echo $this->item->title; ?></h2>
        <p><?php echo $this->item->text; ?></p>
    <?php if (!empty($this->item->created)) : ?>
        <p><strong>Created:</strong> <?php echo $this->item->created; ?></p>
    <?php endif; ?>
    <?php if (!empty($this->item->modified)) : ?>
        <p><strong>Modified:</strong> <?php echo $this->item->modified; ?></p>
    <?php endif; ?>
    <p><em>(Article ID: <?php echo $this->item->id; ?> from <?php echo $this->toolsHelper->buildLink($this->site->url, $this->site->url, true); ?>)</em></p>
</div>
<?php
$back = 'administrator/index.php?option=com_ra_tools&view=standardarticles';
echo $toolsHelper->backButton($back);
