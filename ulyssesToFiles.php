#!/usr/bin/php
<?php

namespace CFPropertyList;

error_reporting(E_ALL ^ E_NOTICE);
ini_set( 'display_errors', 'on' );

require './vendor/autoload.php';

$structure = array();

if (file_exists('Export'))
{
	`rm -r "Export"`;
}

mkdir('Export');

$structure = parseFolder(getenv('HOME').'/Library/Mobile Documents/X5AZV975AG~com~soulmen~ulysses3/Documents/Library');
strucToDisk('./Export', $structure);


function parseFolder($path)
{
	$plist = new CFPropertyList($path.'/Info.ulgroup', CFPropertyList::FORMAT_XML );
	$childFolders = $plist->toArray();
	$structure = array();
	
	if ($childFolders['childOrder'])
	{
		$children = array();
		
		foreach ($childFolders['childOrder'] AS $group)
		{
			if (preg_match('!ulgroup!', $group))
			{
				if (file_exists($path.'/'.$group))
					$children[] = parseFolder($path.'/'.$group);
			}
		}
		
		$structure[$childFolders['displayName'] ? $childFolders['displayName'] : 'Root'] = array(
		   'type' => 'group',
		   'children' => $children,
		);
	}
	else if ($childFolders['sheetClusters'])
	{
		$children = array();
		
		foreach ($childFolders['sheetClusters'] AS $sheets)
		{
			foreach ($sheets AS $sheet)
			{
				if (!file_exists($path.'/'.$sheet)) continue;
				$xmlFile = file_get_contents($path.'/'.$sheet.'/Content.xml');
				$lines = explode("\n", $xmlFile);
		
				$title = '';
				
				foreach ($lines AS $l)
				{
    				if (preg_match('!<tag kind="heading1"!', $l))
					{
						$title = preg_replace('!# ?!', '', strip_tags($l));
						break;
					}
					else if (preg_match('!<tag kind="heading2"!', $l))
					{
						$title = preg_replace('!## ?!', '', strip_tags($l));
						break;
					}
				}
				
				if (!file_exists($path.'/'.$sheet)) continue;
				$fileContents = file_get_contents($path.'/'.$sheet.'/Text.txt');
				
				if (!$title)
				{
    				$firstLine = explode("\n", $fileContents)[0];
    				
    				$title = strlen($firstLine) > 25 ? substr($firstLine, 0, 25).'...' : $firstLine;
				}
				
				$children[] = array(
				   'type' => 'sheet',
				   'title' => $title,
				   'text' => $fileContents,
				);
			}
		}
		
		$structure[$childFolders['displayName']] = array(
	       'type' => 'sheets',
	       'children' => $children,
		);
	}
	
	return $structure;
}

function strucToDisk($path, $struc)
{
	foreach ($struc AS $key => $val)
	{
		$path .= '/'.$key;
		
		if ($key != '')
		{
    		if (file_exists($path))
    		{
    			`rm -r "$path"`;
    		}
    		
    		mkdir($path);
		}

		if ($val['type'] == 'group')
		{
			foreach ($val['children'] AS $child)
			{
				strucToDisk($path, $child);
			}
		}
		else if ($val['type'] == 'sheets')
		{
			foreach ($val['children'] AS $child)
			{
				if ($child['type'] == 'sheet' && $child['title'])
				{
					file_put_contents($path.'/'.filter_filename($child['title']).'.txt', $child['text']);
				}
			}
		}
	}
}

// from: https://stackoverflow.com/a/2021729/28290
function filter_filename($file) {
	// Remove anything which isn't a word, whitespace, number
	// or any of the following caracters -_~,;[]().
	$file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '-', $file);
	// Remove any runs of periods or hyphens
	$file = mb_ereg_replace("([\.-]{2,})", '', $file);

	return $file;
}

?>
