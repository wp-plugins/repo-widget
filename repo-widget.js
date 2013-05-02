jQuery(document).ready(function($) {
    var active_button = $('.rw_first');

    $(".rw_button").click(function() {
        if ($(this) != $(active_button))
        {
            $(active_button).removeClass('rw_active');
            $(this).addClass('rw_active');
            active_button = $(this);

            var input_box = $(this).parent().find('input');
            input_box.val($(this).attr('rel'));
        }
    });
});