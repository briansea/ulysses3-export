#!/usr/bin/php
<?php

namespace CFPropertyList;

error_reporting(E_ALL ^ E_NOTICE);
ini_set( 'display_errors', 'on' );

require_once(__DIR__.'/CFPropertyList/classes/CFPropertyList/CFPropertyList.php');

$structure = array();

$structure = parseFolder(getenv('HOME').'/Library/Containers/com.soulmen.ulysses3/Data/Documents/Library/Groups-ulgroup/');
strucToDisk('./', $structure);

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
				$xmlFile = file_get_contents($path.'/'.$sheet.'/Content.xml');
				$lines = explode("\n", $xmlFile);
				
				foreach ($lines AS $l)
				{
					if (preg_match('!<tag kind="heading2"!', $l))
					{
						$title = str_replace('## ', '', strip_tags($l));
						break;
					}
				}
				
				$children[] = array(
				   'type' => 'sheet',
				   'title' => $title,
				   'text' => file_get_contents($path.'/'.$sheet.'/Text.txt'),
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
		
		if (file_exists($path))
		{
			`rm -r "$path"`;
		}
		
		mkdir($path);
		
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
					file_put_contents($path.'/'.$child['title'].'.txt', $child['text']);
				}
			}
		}
	}
}


?>
