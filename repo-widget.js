jQuery(document).ready(function($) {
    var active_button = $('.links .active');

    $(".links div").click(function() {
        if ($(this) != $(active_button))
        {
            $(active_button).removeClass('active');
            $(this).addClass('active');
            active_button = $(this);

            var input_box = $(this).parent().find('input');
            input_box.val($(this).attr('rel'));
        }
    });
});