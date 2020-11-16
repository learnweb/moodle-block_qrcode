define(['jquery', 'core/modal_factory'], function($, ModalFactory) {

    var show_qr = function(qrcode) {
        ModalFactory.create({
            body: qrcode,
            large: true
        }, undefined)
            .done(function(modal) {
                let child = modal.getRoot().children()[0];
                child.className += " fullsizemodal";
                modal.show();
            });
    };

    var init = function(qrcode) {
        $('#img_qrcode').click(function() {
            show_qr(qrcode);
        });
    };

    return {
            'init': init,
        };
});