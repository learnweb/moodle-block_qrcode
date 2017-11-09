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

<img src="https://user-images.githubusercontent.com/28386141/32593937-42c64a56-c52a-11e7-9361-a04dde2239a1.png" width="320">


### Teachers' view
The teachers see the QR code and the download options.
 If you choose png as file format, you can download a small (150px) or big (300px) image.
 If you choose the svg format you cannot choose a size, because it is a vector graphic that always scales nicely.
 
 
<img src="https://user-images.githubusercontent.com/28386141/32593938-4302321e-c52a-11e7-99f2-455c9eeed2e2.png" width="320">


<img src="https://user-images.githubusercontent.com/28386141/32593936-429850f6-c52a-11e7-9a6f-f3b486582059.png" width="500">

### Administrators' view
The administrator can change the logo. She must upload separate png and svg versions of the same logo,
which are then used in the respective QR code formats.
She can also configure that no logo should be displayed inside the QR code.

<img src="https://user-images.githubusercontent.com/28386141/32319499-15e2f7f6-bfbb-11e7-9bf0-c9f55bd65d5b.png" width="500">

## Background and Motivation
We wanted to have a configuration-free, ready-to-use QR code that leads you into a course when you photograph it. Since it is a block, a teacher only needs to add it his/her your course and then see the code right away (including a "Download" button to include it into introduction slides or to print it as a handout to students). If you leave the block where it is, it will also help students switch devices (e.g. start learning on the computer and continue on a mobile device merely by photographing the QR code).

With more than 2000 courses starting every term, many of them have short names along the lines of IT or SE or other ambiguous abbreviations. Students search for these abbreviations using course search and may get some hundred, rather unrelated, results. This drives up our servers' loads and does not really help our students anyway, which is why we looked for alternative means to get them into their courses for the first time. It might make sense to activate this block by default in all new courses, hoping that teachers will put their QR codes into their introduction slides. This is made (relatively) easy as the block does not require any configuration by teachers.
