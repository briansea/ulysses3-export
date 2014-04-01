ulysses3-export
===============

This PHP script will export your iCloud based groups and sheets to disk, while keeping their hierarchical structure intact. So, group can be in groups, which can be in groups, etc. Ulysses actually has the ability to do something very similar by clicking on a group in the app and then dragging that group to the finder. However, I wanted the ability to automate the process, so that I could use git to version and control my writing. However, I wanted to use iCloud and not external sources. 

## Setup

You will need to download and add https://github.com/rodneyrehm/CFPropertyList to the folder that contains ulyssesToFiles.php

## Running

In terminal, <code>cd</code> into the directory containing the script and run it. You should then see a folder called "Root" created which contains your Ulysses groups and sheets. Then, you can commit them to a respository, backup disk, etc.
