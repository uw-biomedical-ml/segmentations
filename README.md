# HTML5 Canvas based interface to crowdsource image segmentation tasks

The era of deep learning has ushered in a pressing need for human labeled data.
This project allows the creation of image segmentation tasks that can be used
to crowdsource and distribute such tasks. The site uses HTML5 and canvas to record
the mouse positions and timings such that the segmentations can be replayed. In 
addition, it allows for binary masks to be downloaded for machine learning. 

## Features

- Recording of mouse positions with timings.
- HTML5 canvas based implementation.
- Interface for reviewing segmentations to accept or reject segmentations.
- Replication of segmentation tasks such that the same image is not segmented multiple times by the same person and priorization to first create replicated segmentations.

## Prerequistes

- PHP enabled web server
- Mysql database

## Setup

- Clone this repository
- Copy config.php.example to config.php and edit for correct settings.
- Run setup/createdb/php
- Copy images that you want to be segmented into images/
- Run setup/loaddata.php
- Delete or change permissions on setup directory.
- Set permissions on admin/

## Usage

- Direct users to index.php
- See completed segmentations at admin/index.php
- Review to accept or reject segmentations at admin/reviewresult.php
- If needed clear out database by running setup/cleardata.php



