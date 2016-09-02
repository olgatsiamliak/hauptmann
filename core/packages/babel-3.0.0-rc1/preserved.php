<?php return array (
  '2de41da951d79ff1690dbed1c2d5b80d' => 
  array (
    'criteria' => 
    array (
      'name' => 'babel',
    ),
    'object' => 
    array (
      'name' => 'babel',
      'path' => '{core_path}components/babel/',
      'assets_path' => '{assets_path}components/babel/',
    ),
  ),
  '20a836bb0ba25d9f45a7f689674d600f' => 
  array (
    'criteria' => 
    array (
      'text' => 'babel',
    ),
    'object' => 
    array (
      'text' => 'babel',
      'parent' => 'components',
      'action' => 'index',
      'description' => 'babel.desc',
      'icon' => 'images/icons/plugin.gif',
      'menuindex' => 0,
      'params' => '',
      'handler' => '',
      'permissions' => '',
      'namespace' => 'babel',
    ),
  ),
  '2c83eefc4937c408a5c30d848b9002d9' => 
  array (
    'criteria' => 
    array (
      'key' => 'babel.contextKeys',
    ),
    'object' => 
    array (
      'key' => 'babel.contextKeys',
      'value' => 'web,en,pl,de',
      'xtype' => 'textfield',
      'namespace' => 'babel',
      'area' => 'common',
      'editedon' => '2016-08-26 14:42:31',
    ),
  ),
  '67eeb408df735c52e741633ad18d126b' => 
  array (
    'criteria' => 
    array (
      'key' => 'babel.babelTvName',
    ),
    'object' => 
    array (
      'key' => 'babel.babelTvName',
      'value' => 'babelLanguageLinks',
      'xtype' => 'textfield',
      'namespace' => 'babel',
      'area' => 'common',
      'editedon' => NULL,
    ),
  ),
  'c6bba4c0d04a223d9f15970375afc784' => 
  array (
    'criteria' => 
    array (
      'key' => 'babel.syncTvs',
    ),
    'object' => 
    array (
      'key' => 'babel.syncTvs',
      'value' => '',
      'xtype' => 'textfield',
      'namespace' => 'babel',
      'area' => 'common',
      'editedon' => '2016-08-26 16:03:14',
    ),
  ),
  'dbe844c9f2fdd64c6ab2aa823aa4451a' => 
  array (
    'criteria' => 
    array (
      'name' => 'Babel',
    ),
    'object' => 
    array (
      'id' => 10,
      'source' => 0,
      'property_preprocess' => 0,
      'name' => 'Babel',
      'description' => 'Links and synchronizes multilingual resources.',
      'editor_type' => 0,
      'category' => 0,
      'cache_type' => 0,
      'plugincode' => '/**
 * Babel
 *
 * Copyright 2010 by Jakob Class <jakob.class@class-zec.de>
 *
 * This file is part of Babel.
 *
 * Babel is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Babel is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Babel; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package babel
 */
/**
 * Babel Plugin to link and synchronize multilingual resources
 *
 * Based on ideas of Sylvain Aerni <enzyms@gmail.com>
 *
 * Events:
 * OnDocFormPrerender,OnDocFormSave,OnEmptyTrash,OnContextRemove,OnResourceDuplicate
 *
 * @author Jakob Class <jakob.class@class-zec.de>
 *         goldsky <goldsky@virtudraft.com>
 *
 * @package babel
 *
 */

$babel = $modx->getService(\'babel\',\'Babel\',$modx->getOption(\'babel.core_path\',null,$modx->getOption(\'core_path\').\'components/babel/\').\'model/babel/\');

/* be sure babel TV is loaded */
if (!($babel instanceof Babel) || !$babel->babelTv) return;

switch ($modx->event->name) {
	case \'OnDocFormPrerender\':
		$output = \'\';
		$errorMessage = \'\';
		$resource =& $modx->event->params[\'resource\'];
		if(!$resource) {
			/* a new resource is being to created
			 * -> skip rendering the babel box */
			break;
		}
		$linkedResources = $babel->getLinkedResources($resource->get(\'id\'));
		if(empty($linkedResources)) {
			/* always be sure that the Babel TV is set */
			$babel->initBabelTv($resource);
		}

		/* create babel-box with links to translations */
		$outputLanguageItems = \'\';
        if (!$modx->lexicon) {
            $modx->getService(\'lexicon\',\'modLexicon\');
        }
        $languagesStore = array();
		$contextKeys = $babel->getGroupContextKeys($resource->get(\'context_key\'));
		foreach($contextKeys as $contextKey) {
			/* for each (valid/existing) context of the context group a button will be displayed */
			$context = $modx->getObject(\'modContext\', array(\'key\' => $contextKey));
			if(!$context) {
				$modx->log(modX::LOG_LEVEL_ERROR, \'Could not load context: \'.$contextKey);
				continue;
			}
			$context->prepare();
			$cultureKey = $context->getOption(\'cultureKey\',$modx->getOption(\'cultureKey\'));
            $languagesStore[] = array($modx->lexicon(\'babel.language_\'.$cultureKey)." ($contextKey)", $contextKey);
        }

        $babel->config[\'context_key\'] = $resource->get(\'context_key\');
        $babel->config[\'languagesStore\'] = $languagesStore;
        $babel->config[\'menu\'] = $babel->getMenu($resource);
        if (empty($babel->config[\'menu\'])) {
            $modx->log(modX::LOG_LEVEL_ERROR, \'[Babel] Could not load menu for context key: "\' . $babel->config[\'context_key\'] . \'". Try to check "babel.contextKeys" in System Settings. If this is intended, you can ignore this warning.\');
            return;
        }
        $version = str_replace(\' \', \'\', $babel->config[\'version\']);
        $isCSSCompressed = $modx->getOption(\'compress_css\');
        $withVersion = $isCSSCompressed ? \'\' : \'?v=\'.$version;
        $modx->controller->addCss($babel->config[\'cssUrl\'].\'babel.css\'.$withVersion);

        $modx->controller->addLexiconTopic(\'babel:default\');
        $isJsCompressed = $modx->getOption(\'compress_js\');
        $withVersion = $isJsCompressed ? \'\' : \'?v=\'.$version;
        $modx->controller->addJavascript($babel->config[\'jsUrl\'].\'babel.class.js\'.$withVersion);
        $modx->controller->addHtml(\'
<script type="text/javascript">
    Ext.onReady(function () {
        var babel = new Babel(\'.json_encode($babel->config).\');
        babel.getMenu(babel.config.menu);
    });
</script>\');
        break;

	case \'OnDocFormSave\':
		$resource =& $modx->event->params[\'resource\'];
		if(!$resource) {
			$modx->log(modX::LOG_LEVEL_ERROR, \'No resource provided for OnDocFormSave event\');
			break;
		}
		if($modx->event->params[\'mode\'] == modSystemEvent::MODE_NEW) {
			/* no TV synchronization for new resources, just init Babel TV */
			$babel->initBabelTv($resource);
			break;
		}
		$babel->synchronizeTvs($resource->get(\'id\'));
		break;

	case \'OnEmptyTrash\':
		/* remove translation links to non-existing resources */
		$deletedResourceIds =& $modx->event->params[\'ids\'];
		if(is_array($deletedResourceIds)) {
			foreach ($deletedResourceIds as $deletedResourceId) {
				$babel->removeLanguageLinksToResource($deletedResourceId);
			}
		}
		break;

	case \'OnContextRemove\':
		/* remove translation links to non-existing contexts */
		$context =& $modx->event->params[\'context\'];
		if($context) {
			$babel->removeLanguageLinksToContext($context->get(\'key\'));
		}
		break;

	case \'OnResourceDuplicate\':
		/* init Babel TV of duplicated resources */
		$resource =& $modx->event->params[\'newResource\'];
        $babel->initBabelTvsRecursive($modx,$babel,$resource->get(\'id\'));
		break;

    case \'OnResourceSort\':
        $nodesAffected =& $modx->event->params[\'nodesAffected\'];
        foreach ($nodesAffected as $node) {
            $linkedResources = $babel->getLinkedResources($node->get(\'id\'));
            foreach ($linkedResources as $key => $id) {
                if ($id === $node->get(\'id\')) {
                    unset($linkedResources[$key]);
                }
            }
            $linkedResources[$node->get(\'context_key\')] = $node->get(\'id\');
            $babel->updateBabelTv($linkedResources, $linkedResources);
        }

        break;
}
return;',
      'locked' => 0,
      'properties' => NULL,
      'disabled' => 0,
      'moduleguid' => '',
      'static' => 0,
      'static_file' => '',
      'content' => '/**
 * Babel
 *
 * Copyright 2010 by Jakob Class <jakob.class@class-zec.de>
 *
 * This file is part of Babel.
 *
 * Babel is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Babel is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Babel; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package babel
 */
/**
 * Babel Plugin to link and synchronize multilingual resources
 *
 * Based on ideas of Sylvain Aerni <enzyms@gmail.com>
 *
 * Events:
 * OnDocFormPrerender,OnDocFormSave,OnEmptyTrash,OnContextRemove,OnResourceDuplicate
 *
 * @author Jakob Class <jakob.class@class-zec.de>
 *         goldsky <goldsky@virtudraft.com>
 *
 * @package babel
 *
 */

$babel = $modx->getService(\'babel\',\'Babel\',$modx->getOption(\'babel.core_path\',null,$modx->getOption(\'core_path\').\'components/babel/\').\'model/babel/\');

/* be sure babel TV is loaded */
if (!($babel instanceof Babel) || !$babel->babelTv) return;

switch ($modx->event->name) {
	case \'OnDocFormPrerender\':
		$output = \'\';
		$errorMessage = \'\';
		$resource =& $modx->event->params[\'resource\'];
		if(!$resource) {
			/* a new resource is being to created
			 * -> skip rendering the babel box */
			break;
		}
		$linkedResources = $babel->getLinkedResources($resource->get(\'id\'));
		if(empty($linkedResources)) {
			/* always be sure that the Babel TV is set */
			$babel->initBabelTv($resource);
		}

		/* create babel-box with links to translations */
		$outputLanguageItems = \'\';
        if (!$modx->lexicon) {
            $modx->getService(\'lexicon\',\'modLexicon\');
        }
        $languagesStore = array();
		$contextKeys = $babel->getGroupContextKeys($resource->get(\'context_key\'));
		foreach($contextKeys as $contextKey) {
			/* for each (valid/existing) context of the context group a button will be displayed */
			$context = $modx->getObject(\'modContext\', array(\'key\' => $contextKey));
			if(!$context) {
				$modx->log(modX::LOG_LEVEL_ERROR, \'Could not load context: \'.$contextKey);
				continue;
			}
			$context->prepare();
			$cultureKey = $context->getOption(\'cultureKey\',$modx->getOption(\'cultureKey\'));
            $languagesStore[] = array($modx->lexicon(\'babel.language_\'.$cultureKey)." ($contextKey)", $contextKey);
        }

        $babel->config[\'context_key\'] = $resource->get(\'context_key\');
        $babel->config[\'languagesStore\'] = $languagesStore;
        $babel->config[\'menu\'] = $babel->getMenu($resource);
        if (empty($babel->config[\'menu\'])) {
            $modx->log(modX::LOG_LEVEL_ERROR, \'[Babel] Could not load menu for context key: "\' . $babel->config[\'context_key\'] . \'". Try to check "babel.contextKeys" in System Settings. If this is intended, you can ignore this warning.\');
            return;
        }
        $version = str_replace(\' \', \'\', $babel->config[\'version\']);
        $isCSSCompressed = $modx->getOption(\'compress_css\');
        $withVersion = $isCSSCompressed ? \'\' : \'?v=\'.$version;
        $modx->controller->addCss($babel->config[\'cssUrl\'].\'babel.css\'.$withVersion);

        $modx->controller->addLexiconTopic(\'babel:default\');
        $isJsCompressed = $modx->getOption(\'compress_js\');
        $withVersion = $isJsCompressed ? \'\' : \'?v=\'.$version;
        $modx->controller->addJavascript($babel->config[\'jsUrl\'].\'babel.class.js\'.$withVersion);
        $modx->controller->addHtml(\'
<script type="text/javascript">
    Ext.onReady(function () {
        var babel = new Babel(\'.json_encode($babel->config).\');
        babel.getMenu(babel.config.menu);
    });
</script>\');
        break;

	case \'OnDocFormSave\':
		$resource =& $modx->event->params[\'resource\'];
		if(!$resource) {
			$modx->log(modX::LOG_LEVEL_ERROR, \'No resource provided for OnDocFormSave event\');
			break;
		}
		if($modx->event->params[\'mode\'] == modSystemEvent::MODE_NEW) {
			/* no TV synchronization for new resources, just init Babel TV */
			$babel->initBabelTv($resource);
			break;
		}
		$babel->synchronizeTvs($resource->get(\'id\'));
		break;

	case \'OnEmptyTrash\':
		/* remove translation links to non-existing resources */
		$deletedResourceIds =& $modx->event->params[\'ids\'];
		if(is_array($deletedResourceIds)) {
			foreach ($deletedResourceIds as $deletedResourceId) {
				$babel->removeLanguageLinksToResource($deletedResourceId);
			}
		}
		break;

	case \'OnContextRemove\':
		/* remove translation links to non-existing contexts */
		$context =& $modx->event->params[\'context\'];
		if($context) {
			$babel->removeLanguageLinksToContext($context->get(\'key\'));
		}
		break;

	case \'OnResourceDuplicate\':
		/* init Babel TV of duplicated resources */
		$resource =& $modx->event->params[\'newResource\'];
        $babel->initBabelTvsRecursive($modx,$babel,$resource->get(\'id\'));
		break;

    case \'OnResourceSort\':
        $nodesAffected =& $modx->event->params[\'nodesAffected\'];
        foreach ($nodesAffected as $node) {
            $linkedResources = $babel->getLinkedResources($node->get(\'id\'));
            foreach ($linkedResources as $key => $id) {
                if ($id === $node->get(\'id\')) {
                    unset($linkedResources[$key]);
                }
            }
            $linkedResources[$node->get(\'context_key\')] = $node->get(\'id\');
            $babel->updateBabelTv($linkedResources, $linkedResources);
        }

        break;
}
return;',
    ),
  ),
  '2bf0f10e1062f52e9d2da7ff48d18d26' => 
  array (
    'criteria' => 
    array (
      'pluginid' => 10,
      'event' => 'OnDocFormPrerender',
    ),
    'object' => 
    array (
      'pluginid' => 10,
      'event' => 'OnDocFormPrerender',
      'priority' => 0,
      'propertyset' => 0,
    ),
  ),
  'e06eaed873ef777424323d834bcd6b40' => 
  array (
    'criteria' => 
    array (
      'pluginid' => 10,
      'event' => 'OnDocFormSave',
    ),
    'object' => 
    array (
      'pluginid' => 10,
      'event' => 'OnDocFormSave',
      'priority' => 0,
      'propertyset' => 0,
    ),
  ),
  'd6229613e0f4617cabcc390b98c1d8cd' => 
  array (
    'criteria' => 
    array (
      'pluginid' => 10,
      'event' => 'OnEmptyTrash',
    ),
    'object' => 
    array (
      'pluginid' => 10,
      'event' => 'OnEmptyTrash',
      'priority' => 0,
      'propertyset' => 0,
    ),
  ),
  'bed7c7df00f6f74293821927018f428e' => 
  array (
    'criteria' => 
    array (
      'pluginid' => 10,
      'event' => 'OnContextRemove',
    ),
    'object' => 
    array (
      'pluginid' => 10,
      'event' => 'OnContextRemove',
      'priority' => 0,
      'propertyset' => 0,
    ),
  ),
  '580979a90426ee594d592beb2ce53315' => 
  array (
    'criteria' => 
    array (
      'pluginid' => 10,
      'event' => 'OnResourceDuplicate',
    ),
    'object' => 
    array (
      'pluginid' => 10,
      'event' => 'OnResourceDuplicate',
      'priority' => 0,
      'propertyset' => 0,
    ),
  ),
  '7dbc15001323b217eab72a2e022c4f49' => 
  array (
    'criteria' => 
    array (
      'pluginid' => 10,
      'event' => 'OnResourceSort',
    ),
    'object' => 
    array (
      'pluginid' => 10,
      'event' => 'OnResourceSort',
      'priority' => 0,
      'propertyset' => 0,
    ),
  ),
  '8d9fdd785645569fc843962642215b3d' => 
  array (
    'criteria' => 
    array (
      'category' => 'Babel',
    ),
    'object' => 
    array (
      'id' => 13,
      'parent' => 0,
      'category' => 'Babel',
      'rank' => 0,
    ),
  ),
  'fb69e335befe6c464299d24595adc9fd' => 
  array (
    'criteria' => 
    array (
      'name' => 'BabelLinks',
    ),
    'object' => 
    array (
      'id' => 44,
      'source' => 0,
      'property_preprocess' => 0,
      'name' => 'BabelLinks',
      'description' => 'Displays links to translated resources.',
      'editor_type' => 0,
      'category' => 13,
      'cache_type' => 0,
      'snippet' => '/**
 * Babel
 *
 * Copyright 2010 by Jakob Class <jakob.class@class-zec.de>
 *
 * This file is part of Babel.
 *
 * Babel is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Babel is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Babel; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package babel
 */
/**
 * BabelLinks snippet to display links to translated resources
 *
 * Based on ideas of Sylvain Aerni <enzyms@gmail.com>
 *
 * @author Jakob Class <jakob.class@class-zec.de>
 *         goldsky <goldsky@virtudraft.com>
 *
 * @package babel
 *
 * @param resourceId        optional: id of resource of which links to translations should be displayed. Default: current resource
 * @param tpl               optional: Chunk to display a language link. Default: babelLink
 * @param activeCls         optional: CSS class name for the current active language. Default: active
 * @param showUnpublished   optional: flag whether to show unpublished translations. Default: 0
 * @param showCurrent       optional: flag whether to show a link to a translation of the current language. Default: 1
 */
$babel = $modx->getService(\'babel\',\'Babel\',$modx->getOption(\'babel.core_path\',null,$modx->getOption(\'core_path\').\'components/babel/\').\'model/babel/\',$scriptProperties);

/* be sure babel and babel TV is loaded */
if (!($babel instanceof Babel) || !$babel->babelTv) return;

/* get snippet properties */
$resourceId = intval($modx->getOption(\'resourceId\',$scriptProperties));
if (empty($resourceId)) {
    if (!empty($modx->resource) && is_object($modx->resource)) {
        $resourceId = $modx->resource->get(\'id\');
    } else {
        return;
    }
}
$tpl = $modx->getOption(\'tpl\',$scriptProperties,\'babelLink\');
$wrapperTpl = $modx->getOption(\'wrapperTpl\',$scriptProperties);
$activeCls = $modx->getOption(\'activeCls\',$scriptProperties,\'active\');
$showUnpublished = $modx->getOption(\'showUnpublished\',$scriptProperties,0);
$showCurrent = $modx->getOption(\'showCurrent\',$scriptProperties,0);
$outputSeparator = $modx->getOption(\'outputSeparator\',$scriptProperties,"\\n");
$includeUnlinked = $modx->getOption(\'includeUnlinked\',$scriptProperties,0);
$ignoreSiteStatus = $modx->getOption(\'ignoreSiteStatus\',$scriptProperties,0);

if(!empty($modx->resource) && is_object($modx->resource) && $resourceId === $modx->resource->get(\'id\')) {
    $contextKeys = $babel->getGroupContextKeys($modx->resource->get(\'context_key\'));
    $resource = $modx->resource;
} else {
    $resource = $modx->getObject(\'modResource\', $resourceId);
    if(!$resource) {
        return;
    }
    $contextKeys = $babel->getGroupContextKeys($resource->get(\'context_key\'));
}

$linkedResources = $babel->getLinkedResources($resourceId);

$outputArray = array();
foreach($contextKeys as $contextKey) {
    if(!$showCurrent && $contextKey === $resource->get(\'context_key\')) {
        continue;
    }
    if (!$includeUnlinked && !isset($linkedResources[$contextKey])) {
        continue;
    }
    $context = $modx->getObject(\'modContext\', array(\'key\' => $contextKey));
    if(!$context) {
        $modx->log(modX::LOG_LEVEL_ERROR, \'Could not load context: \'.$contextKey);
        continue;
    }
    $context->prepare();
    if (!$context->getOption(\'site_status\', null, true) && !$ignoreSiteStatus) {
        continue;
    }
    $cultureKey = $context->getOption(\'cultureKey\',$modx->getOption(\'cultureKey\'));
    $translationAvailable = false;
    if(isset($linkedResources[$contextKey])) {
        $c = $modx->newQuery(\'modResource\');
        $c->where(array(
            \'id\' => $linkedResources[$contextKey],
            \'deleted:!=\' => 1,
            \'published:=\' => 1,
        ));
        if ($showUnpublished) {
            $c->where(array(
                \'OR:published:=\' => 0,
            ));
        }
        $count = $modx->getCount(\'modResource\',$c);
        if($count) {
            $translationAvailable = true;
        }
    }
    $getRequest = $_GET;
    unset($getRequest[\'id\']);
    unset($getRequest[$modx->getOption(\'request_param_alias\', null, \'q\')]);
    unset($getRequest[\'cultureKey\']);
    if($translationAvailable) {
        $url = $context->makeUrl($linkedResources[$contextKey],$getRequest,\'full\');
        $active = ($resource->get(\'context_key\') == $contextKey) ? $activeCls : \'\';
        $placeholders = array(
            \'cultureKey\' => $cultureKey,
            \'url\' => $url,
            \'active\' => $active,
            \'id\' => $linkedResources[$contextKey]
        );

        if (!empty($toArray)) {
            $outputArray[] = $placeholders;
        } else {
            $chunk = $babel->getChunk($tpl,$placeholders);
            if(!empty($chunk)) {
                $outputArray[] = $chunk;
            }
        }
    } elseif ($includeUnlinked) {
        $url = $context->makeUrl($context->getOption(\'site_start\'),$getRequest,\'full\');
        $active = ($resource->get(\'context_key\') == $contextKey) ? $activeCls : \'\';
        $placeholders = array(
            \'cultureKey\' => $cultureKey,
            \'url\' => $url,
            \'active\' => $active,
            \'id\' => $context->getOption(\'site_start\')
        );

        if (!empty($toArray)) {
            $outputArray[] = $placeholders;
        } else {
            $chunk = $babel->getChunk($tpl,$placeholders);
            if(!empty($chunk)) {
                $outputArray[] = $chunk;
            }
        }
    }
}

if (!empty($toArray)) {
    return \'<pre>\'.  print_r($outputArray, 1).\'</pre>\';
}

$output = implode($outputSeparator, $outputArray);
if (!empty($wrapperTpl)) {
    $output = $babel->getChunk($wrapperTpl,array(
        \'babelLinks\' => $output
    ));
}

if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
    return;
}

return $output;',
      'locked' => 0,
      'properties' => 'a:6:{s:10:"resourceId";a:7:{s:4:"name";s:10:"resourceId";s:4:"desc";s:21:"babellinks.resourceId";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:16:"babel:properties";s:4:"area";s:0:"";}s:3:"tpl";a:7:{s:4:"name";s:3:"tpl";s:4:"desc";s:14:"babellinks.tpl";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:9:"babelLink";s:7:"lexicon";s:16:"babel:properties";s:4:"area";s:0:"";}s:9:"activeCls";a:7:{s:4:"name";s:9:"activeCls";s:4:"desc";s:20:"babellinks.activeCls";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:6:"active";s:7:"lexicon";s:16:"babel:properties";s:4:"area";s:0:"";}s:15:"showUnpublished";a:7:{s:4:"name";s:15:"showUnpublished";s:4:"desc";s:26:"babellinks.showUnpublished";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:1:"0";s:7:"lexicon";s:16:"babel:properties";s:4:"area";s:0:"";}s:11:"showCurrent";a:7:{s:4:"name";s:11:"showCurrent";s:4:"desc";s:22:"babellinks.showCurrent";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:1:"0";s:7:"lexicon";s:16:"babel:properties";s:4:"area";s:0:"";}s:15:"includeUnlinked";a:7:{s:4:"name";s:15:"includeUnlinked";s:4:"desc";s:26:"babellinks.includeUnlinked";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:1:"0";s:7:"lexicon";s:16:"babel:properties";s:4:"area";s:0:"";}}',
      'moduleguid' => '',
      'static' => 0,
      'static_file' => '',
      'content' => '/**
 * Babel
 *
 * Copyright 2010 by Jakob Class <jakob.class@class-zec.de>
 *
 * This file is part of Babel.
 *
 * Babel is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Babel is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Babel; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package babel
 */
/**
 * BabelLinks snippet to display links to translated resources
 *
 * Based on ideas of Sylvain Aerni <enzyms@gmail.com>
 *
 * @author Jakob Class <jakob.class@class-zec.de>
 *         goldsky <goldsky@virtudraft.com>
 *
 * @package babel
 *
 * @param resourceId        optional: id of resource of which links to translations should be displayed. Default: current resource
 * @param tpl               optional: Chunk to display a language link. Default: babelLink
 * @param activeCls         optional: CSS class name for the current active language. Default: active
 * @param showUnpublished   optional: flag whether to show unpublished translations. Default: 0
 * @param showCurrent       optional: flag whether to show a link to a translation of the current language. Default: 1
 */
$babel = $modx->getService(\'babel\',\'Babel\',$modx->getOption(\'babel.core_path\',null,$modx->getOption(\'core_path\').\'components/babel/\').\'model/babel/\',$scriptProperties);

/* be sure babel and babel TV is loaded */
if (!($babel instanceof Babel) || !$babel->babelTv) return;

/* get snippet properties */
$resourceId = intval($modx->getOption(\'resourceId\',$scriptProperties));
if (empty($resourceId)) {
    if (!empty($modx->resource) && is_object($modx->resource)) {
        $resourceId = $modx->resource->get(\'id\');
    } else {
        return;
    }
}
$tpl = $modx->getOption(\'tpl\',$scriptProperties,\'babelLink\');
$wrapperTpl = $modx->getOption(\'wrapperTpl\',$scriptProperties);
$activeCls = $modx->getOption(\'activeCls\',$scriptProperties,\'active\');
$showUnpublished = $modx->getOption(\'showUnpublished\',$scriptProperties,0);
$showCurrent = $modx->getOption(\'showCurrent\',$scriptProperties,0);
$outputSeparator = $modx->getOption(\'outputSeparator\',$scriptProperties,"\\n");
$includeUnlinked = $modx->getOption(\'includeUnlinked\',$scriptProperties,0);
$ignoreSiteStatus = $modx->getOption(\'ignoreSiteStatus\',$scriptProperties,0);

if(!empty($modx->resource) && is_object($modx->resource) && $resourceId === $modx->resource->get(\'id\')) {
    $contextKeys = $babel->getGroupContextKeys($modx->resource->get(\'context_key\'));
    $resource = $modx->resource;
} else {
    $resource = $modx->getObject(\'modResource\', $resourceId);
    if(!$resource) {
        return;
    }
    $contextKeys = $babel->getGroupContextKeys($resource->get(\'context_key\'));
}

$linkedResources = $babel->getLinkedResources($resourceId);

$outputArray = array();
foreach($contextKeys as $contextKey) {
    if(!$showCurrent && $contextKey === $resource->get(\'context_key\')) {
        continue;
    }
    if (!$includeUnlinked && !isset($linkedResources[$contextKey])) {
        continue;
    }
    $context = $modx->getObject(\'modContext\', array(\'key\' => $contextKey));
    if(!$context) {
        $modx->log(modX::LOG_LEVEL_ERROR, \'Could not load context: \'.$contextKey);
        continue;
    }
    $context->prepare();
    if (!$context->getOption(\'site_status\', null, true) && !$ignoreSiteStatus) {
        continue;
    }
    $cultureKey = $context->getOption(\'cultureKey\',$modx->getOption(\'cultureKey\'));
    $translationAvailable = false;
    if(isset($linkedResources[$contextKey])) {
        $c = $modx->newQuery(\'modResource\');
        $c->where(array(
            \'id\' => $linkedResources[$contextKey],
            \'deleted:!=\' => 1,
            \'published:=\' => 1,
        ));
        if ($showUnpublished) {
            $c->where(array(
                \'OR:published:=\' => 0,
            ));
        }
        $count = $modx->getCount(\'modResource\',$c);
        if($count) {
            $translationAvailable = true;
        }
    }
    $getRequest = $_GET;
    unset($getRequest[\'id\']);
    unset($getRequest[$modx->getOption(\'request_param_alias\', null, \'q\')]);
    unset($getRequest[\'cultureKey\']);
    if($translationAvailable) {
        $url = $context->makeUrl($linkedResources[$contextKey],$getRequest,\'full\');
        $active = ($resource->get(\'context_key\') == $contextKey) ? $activeCls : \'\';
        $placeholders = array(
            \'cultureKey\' => $cultureKey,
            \'url\' => $url,
            \'active\' => $active,
            \'id\' => $linkedResources[$contextKey]
        );

        if (!empty($toArray)) {
            $outputArray[] = $placeholders;
        } else {
            $chunk = $babel->getChunk($tpl,$placeholders);
            if(!empty($chunk)) {
                $outputArray[] = $chunk;
            }
        }
    } elseif ($includeUnlinked) {
        $url = $context->makeUrl($context->getOption(\'site_start\'),$getRequest,\'full\');
        $active = ($resource->get(\'context_key\') == $contextKey) ? $activeCls : \'\';
        $placeholders = array(
            \'cultureKey\' => $cultureKey,
            \'url\' => $url,
            \'active\' => $active,
            \'id\' => $context->getOption(\'site_start\')
        );

        if (!empty($toArray)) {
            $outputArray[] = $placeholders;
        } else {
            $chunk = $babel->getChunk($tpl,$placeholders);
            if(!empty($chunk)) {
                $outputArray[] = $chunk;
            }
        }
    }
}

if (!empty($toArray)) {
    return \'<pre>\'.  print_r($outputArray, 1).\'</pre>\';
}

$output = implode($outputSeparator, $outputArray);
if (!empty($wrapperTpl)) {
    $output = $babel->getChunk($wrapperTpl,array(
        \'babelLinks\' => $output
    ));
}

if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
    return;
}

return $output;',
    ),
  ),
  '3da61f23c7dabb9ea2dfe6c784266c11' => 
  array (
    'criteria' => 
    array (
      'name' => 'BabelTranslation',
    ),
    'object' => 
    array (
      'id' => 45,
      'source' => 0,
      'property_preprocess' => 0,
      'name' => 'BabelTranslation',
      'description' => 'Returns the id of a translated resource in a given context.',
      'editor_type' => 0,
      'category' => 13,
      'cache_type' => 0,
      'snippet' => '/**
 * Babel
 *
 * Copyright 2010 by Jakob Class <jakob.class@class-zec.de>
 *
 * This file is part of Babel.
 *
 * Babel is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Babel is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Babel; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package babel
 */
/**
 * BabelTranslation snippet to get the id of a translated resource in a given context.
 *
 * @author Jakob Class <jakob.class@class-zec.de>
 *
 * @package babel
 *
 * @param resourceId		optional: id of resource of which a translated resource should be determined. Default: current resource
 * @param contextKey		optional: Key of context in which translated resource should be determined.
 * @param cultureKey		optional: Key of culture in which translated resource should be determined. Used only in case contextKey was not specified.  If both omitted: uses currently set cultureKey.
 * @param showUnpublished	optional: flag whether to show unpublished translations. Default: 0
 */
$babel = $modx->getService(\'babel\', \'Babel\', $modx->getOption(\'babel.core_path\', null, $modx->getOption(\'core_path\') . \'components/babel/\') . \'model/babel/\', $scriptProperties);

/* be sure babel and babel TV is loaded */
if (!($babel instanceof Babel) || !$babel->babelTv)
    return;

/* get snippet properties */
$resourceId = intval($modx->getOption(\'resourceId\', $scriptProperties));
if (empty($resourceId)) {
    if (!empty($modx->resource) && is_object($modx->resource)) {
        $resourceId = $modx->resource->get(\'id\');
    } else {
        return;
    }
}
$contextKey = $modx->getOption(\'contextKey\', $scriptProperties, \'\');
if (empty($contextKey)) {
    $cultureKey = $modx->getOption(\'cultureKey\', $scriptProperties, \'\');
    $contextKey = $babel->getContextKey($cultureKey);
}
$showUnpublished = $modx->getOption(\'showUnpublished\', $scriptProperties, 0);

/* determine id of tranlated resource */
$linkedResources = $babel->getLinkedResources($resourceId);
$output = null;
if (isset($linkedResources[$contextKey])) {
    $resource = $modx->getObject(\'modResource\', $linkedResources[$contextKey]);
    if ($resource && ($showUnpublished || $resource->get(\'published\') == 1)) {
        $output = $resource->get(\'id\');
    }
}
return $output;',
      'locked' => 0,
      'properties' => 'a:4:{s:10:"resourceId";a:7:{s:4:"name";s:10:"resourceId";s:4:"desc";s:27:"babeltranslation.resourceId";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:16:"babel:properties";s:4:"area";s:0:"";}s:10:"contextKey";a:7:{s:4:"name";s:10:"contextKey";s:4:"desc";s:27:"babeltranslation.contextKey";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:16:"babel:properties";s:4:"area";s:0:"";}s:10:"cultureKey";a:7:{s:4:"name";s:10:"cultureKey";s:4:"desc";s:27:"babeltranslation.cultureKey";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:0:"";s:7:"lexicon";s:16:"babel:properties";s:4:"area";s:0:"";}s:15:"showUnpublished";a:7:{s:4:"name";s:15:"showUnpublished";s:4:"desc";s:32:"babeltranslation.showUnpublished";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:1:"0";s:7:"lexicon";s:16:"babel:properties";s:4:"area";s:0:"";}}',
      'moduleguid' => '',
      'static' => 0,
      'static_file' => '',
      'content' => '/**
 * Babel
 *
 * Copyright 2010 by Jakob Class <jakob.class@class-zec.de>
 *
 * This file is part of Babel.
 *
 * Babel is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Babel is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Babel; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package babel
 */
/**
 * BabelTranslation snippet to get the id of a translated resource in a given context.
 *
 * @author Jakob Class <jakob.class@class-zec.de>
 *
 * @package babel
 *
 * @param resourceId		optional: id of resource of which a translated resource should be determined. Default: current resource
 * @param contextKey		optional: Key of context in which translated resource should be determined.
 * @param cultureKey		optional: Key of culture in which translated resource should be determined. Used only in case contextKey was not specified.  If both omitted: uses currently set cultureKey.
 * @param showUnpublished	optional: flag whether to show unpublished translations. Default: 0
 */
$babel = $modx->getService(\'babel\', \'Babel\', $modx->getOption(\'babel.core_path\', null, $modx->getOption(\'core_path\') . \'components/babel/\') . \'model/babel/\', $scriptProperties);

/* be sure babel and babel TV is loaded */
if (!($babel instanceof Babel) || !$babel->babelTv)
    return;

/* get snippet properties */
$resourceId = intval($modx->getOption(\'resourceId\', $scriptProperties));
if (empty($resourceId)) {
    if (!empty($modx->resource) && is_object($modx->resource)) {
        $resourceId = $modx->resource->get(\'id\');
    } else {
        return;
    }
}
$contextKey = $modx->getOption(\'contextKey\', $scriptProperties, \'\');
if (empty($contextKey)) {
    $cultureKey = $modx->getOption(\'cultureKey\', $scriptProperties, \'\');
    $contextKey = $babel->getContextKey($cultureKey);
}
$showUnpublished = $modx->getOption(\'showUnpublished\', $scriptProperties, 0);

/* determine id of tranlated resource */
$linkedResources = $babel->getLinkedResources($resourceId);
$output = null;
if (isset($linkedResources[$contextKey])) {
    $resource = $modx->getObject(\'modResource\', $linkedResources[$contextKey]);
    if ($resource && ($showUnpublished || $resource->get(\'published\') == 1)) {
        $output = $resource->get(\'id\');
    }
}
return $output;',
    ),
  ),
);