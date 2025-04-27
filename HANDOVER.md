# Handover Notes
Created by: Jeremy Layson

For any follow-up question or clarification, you can email me at [jeremy.b.layson@gmail.com](mailto:jeremy.b.layson@gmail.com)

## Loading Animation Issue

When updating a form, the loading animation just covers half of the actual form. This issue has been present since the beginning but it seems to be not important to fix for now. I have observed this in VA but I am positive this is a Fila-CMS issue.

## Media Upload Issue

Media Upload sometimes takes a bit of time to load the file tree, and even when selecting it, it's sometimes not clear that you already selected a file. Some performance improvement and display changes could be done here.

## Using Fila-CMS on new project

I have tried creating a new Laravel project that uses Fila-CMS, I was able to successfully installed it after fixing few things here and there, but I wasn't able to perform a full test on all of its feature so there's a possibility that I have missed something and that Fila-CMS cannot be integrated smoothly on new projects.

## Test Cases

Currently, it uses `WorkBench` for its testing, but it seems it has been disabled by Kath in the past for the reason that it probably isn't working as intended yet. It might be a good idea to revisit how the test cases can be reintegrated on Fila-CMS itself.