<?php
/**
* NOTICE OF LICENSE
*
* Copyright 2012 Guidance Solutions
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
* http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.

* @author Gordon Knoppe
* @category Guidance
* @package Magecheck
* @copyright Copyright (c) 2012 Guidance Solutions (http://www.guidance.com)
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
*/

// Initialize Magento
require 'app/Mage.php';
Mage::app('admin', 'store');

function getRewrites($classType, $sorted = true)
{
    $config = Mage::getConfig();
    $configNode = 'global/' . $classType;
    $models = $config->getNode($configNode)->asArray();
    $rewrites = array();
    foreach ($models as $package => $config) {
        if (isset($config['rewrite'])) {
            foreach ($config['rewrite'] as $alias => $class) {
                $classAlias = $package . '/' . $alias;
                $rewrites[$classAlias] = $class;
            }
        }
    }
    if ($sorted) {
        ksort($rewrites);
    }
    return $rewrites;
}

function getModules($sorted = true)
{
    $config = Mage::getConfig();
    $configNode = 'modules';
    $modules = $config->getNode($configNode)->asArray();
    $codePools = array();
    foreach ($modules as $package => $config) {
        if (isset($config['codePool'])) {
            $codePool = $config['codePool'];
            $codePools[$codePool][] = $package;
        }
    }
    if ($sorted) {
        foreach (array_keys($codePools) as $codePool) {
            sort($codePools[$codePool]);
        }
    }
    return $codePools;
}

$codePools = getModules();


$rewriteTypes = array('blocks', 'helpers', 'models');
$systemRewrites = array();
foreach ($rewriteTypes as $rewriteType) {
    $systemRewrites[$rewriteType] = getRewrites($rewriteType);
}

?>
<html>
<head>
    <title><?php echo $_SERVER['HTTP_HOST'];?> - Magento Audit Report</title>
    <style type="text/css">
        body {
            font-family: sans-serif;
        }
        ul {
            padding-left: 0;
        }
        label, input {
            display: block;
            margin-bottom: 5px;
        }
        .alert {
            background-color: #FCF8E3;
            border: 1px solid #FBEED5;
            border-radius: 4px 4px 4px 4px;
            color: #C09853;
            margin-bottom: 20px;
            padding: 8px 35px 8px 14px;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
            list-style: none;
        }
        .alert-success {
            background-color: #DFF0D8;
            border-color: #D6E9C6;
            color: #468847;
        }
        .alert-error {
            background-color: #F2DEDE;
            border-color: #EED3D7;
            color: #B94A48;
        }
        th, td {
            border: 1px solid #e8e8e8;
            padding: .5em
        }
        table.summary {
            min-width: 300px;
        }
        table.summary td {
            height: 40px;
            text-align: right;
        }
        table.summary th {
            text-align: left;
        }
        table.modules {
            width: 100%;
        }
        table.modules td {
            padding-bottom: 100px;
        }
        table.rewrites {
            width: 100%;
        }
        table.rewrites td {
            padding-bottom: 100px;
        }
        table.rewrites p {
            font-weight: bold;
        }
    </style>
</head>
<body>
<h1>Magento Module Audit Report</h1>
<h2>Summary:</h2>
<table class="summary">
    <tr>
        <th>Community modules</th>
        <td><?php echo count($codePools['community']); ?></td>
    </tr>
    <tr>
        <th>Local modules</th>
        <td><?php echo count($codePools['local']); ?></td>
    </tr>
    <tr>
        <th>Block rewrites</th>
        <td><?php echo count($systemRewrites['blocks']); ?></td>
    </tr>
    <tr>
        <th>Helper rewrites</th>
        <td><?php echo count($systemRewrites['helpers']); ?></td>
    </tr>
    <tr>
        <th>Model rewrites</th>
        <td><?php echo count($systemRewrites['models']); ?></td>
    </tr>
</table>

<h2>Installed modules:</h2>

<?php foreach ($codePools as $codePoolType => $modules): ?>

<?php if ($codePoolType == 'core') continue; ?>

<h3><?php echo ucwords($codePoolType); ?> code pool</h3>

    <?php if (count($modules)): ?>
        <table class="modules">
            <col width="25%" />
            <col width="75%" />
            <tr>
                <th>Module Name</th>
                <th>Purpose</th>
            </tr>
        <?php foreach ($modules as $moduleName): ?>
            <tr>
                <td><?php echo $moduleName; ?></td>
                <td>&nbsp;</td>
            </tr>
        <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No modules found</p>
    <?php endif; ?>

<?php endforeach; ?>

<h2>Class rewrites:</h2>

<?php foreach ($systemRewrites as $rewriteType => $rewrites): ?>

<h3><?php echo ucwords($rewriteType); ?></h3>

    <?php if (count($rewrites)): ?>
        <?php foreach ($rewrites as $alias => $class): ?>
            <table class="rewrites">
                <tr>
                    <td>
                        <p><?php echo $alias; ?> =&gt; <?php echo $class; ?></p>
                        <p>Purpose:</p>
                    </td>
                </tr>
            </table>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No rewrites found</p>
    <?php endif; ?>

<?php endforeach; ?>

</body>
</html>