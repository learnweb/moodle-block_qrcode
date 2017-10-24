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

<img src="https://user-images.githubusercontent.com/28386141/29065683-2977f7ca-7c2d-11e7-9148-fcfbb9a640d4.png" width="320">


### Teachers' view
The teachers see the QR code and the download options.
 If you choose png as file format, you can download a small (150px) or big (300px) image.
 If you choose the svg format you cannot choose a size, because it is a vector graphic that always scales nicely.
 
 
<img src="https://user-images.githubusercontent.com/28386141/29065682-297556fa-7c2d-11e7-8d56-fe6fff0f77cd.png" width="320">


<img src="https://user-images.githubusercontent.com/28386141/29065685-297cfbb2-7c2d-11e7-844c-c2be4638b347.png" width="500">

### Administrators' view
The administrator can change the logo. She must upload separate png and svg versions of the same logo,
which are then used in the respective QR code formats.
She can also configure that no logo should be displayed inside the QR code.

<img src="https://user-images.githubusercontent.com/28386141/29065684-297cea96-7c2d-11e7-9e3e-a6456ae5a6e5.png" width="500">
