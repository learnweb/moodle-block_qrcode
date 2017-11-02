# Block: QR Code 
[![Build Status](https://travis-ci.org/learnweb/moodle-block_qrcode.svg?branch=master)](https://travis-ci.org/learnweb/moodle-block_qrcode)

A moodle block to display a QR code link that leads to the course page. The QR code can be downloaded by teachers, e.g. to put them into introductory slides.
Teachers can download the QR code as svg or png files. 
The administrator can upload a custom logo that is displayed in the center of the QR code, defaulting to 
the Moodle logo if none is specified.

This plugin is developed by [Tamara Gunkel](https://github.com/TamaraGunkel) and is maintained by Learnweb (University of Münster).

## Installation
This plugin should go into `blocks/qrcode`. Afterwards, a custom logo may be defined (see [Administrators' view](#administrators-view))

## Screenshots

### Students' view
The students can only see the QR code. 

<img src="https://user-images.githubusercontent.com/28386141/32319501-164a9758-bfbb-11e7-9b63-09dc6f8517d0.png" width="320">


### Teachers' view
The teachers see the QR code and the download options.
 If you choose png as file format, you can download a small (150px) or big (300px) image.
 If you choose the svg format you cannot choose a size, because it is a vector graphic that always scales nicely.
 
 
<img src="https://user-images.githubusercontent.com/28386141/32319502-167e51ba-bfbb-11e7-8f59-2477367669c6.png" width="320">


<img src="https://user-images.githubusercontent.com/28386141/32319500-160e8a1a-bfbb-11e7-93f7-76f7a7aa48a6.png" width="500">

### Administrators' view
The administrator can change the logo. She must upload separate png and svg versions of the same logo,
which are then used in the respective QR code formats.
She can also configure that no logo should be displayed inside the QR code.

<img src="https://user-images.githubusercontent.com/28386141/32319499-15e2f7f6-bfbb-11e7-9bf0-c9f55bd65d5b.png" width="500">
