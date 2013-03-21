
(function($) { // Undo noConflict()

function in_array(needle, haystack, argStrict) {

    var key = '', strict = !!argStrict;
    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true;
            }
        }
    } else {
        for (key in haystack) {
            if (haystack[key] == needle) {
                return true;
            }
        }
    }
    return false;
}

function autoselect(dd_arr) {
    var reg = new Array();
    reg[1] = "#get_team_selector_south_";
    reg[2] = "#get_team_selector_west_";
    reg[3] = "#get_team_selector_east_";
    reg[4] = "#get_team_selector_midwest_";


    var ret = new Array();
    var j = 0;
    for (var i = 0; i < dd_arr.length; i++) {
        for (var l = 1; l <= reg.length; l++) {
            var dd_length = $(reg[l] + dd_arr[i].match_ident).children('option').length;
            if (dd_length == 1) {
                ret[j] = dd_arr[i];
                j++;
            }
            else {
                $(reg[l] + dd_arr[i].match_ident).val(dd_arr[i].winner_id);
            }
        }
    }

    return     ret;

}

function fill_in_the_dropdown(dropdowns) {

    dropdowns.each(function () {
        var p = $(this);
        var sid3 = p.attr('id');
        var temp = sid3.split('_');
        var mid2 = temp[4];

        $('#' + mid2 + ' > p > select').each(function () {
            if ($(this).val() != 0) {
                p.append($(this).find('option:selected').clone().attr('id', $(this).val()));
            }
        });
    });

}

// NLT, set team selections in final bracket
function set_team(match_id, slot, team_id, winner) {

    if (! team_id) return;

    var seed = team_id % 100;

    // Just pull the name from the original seeds. All is the same except possibly slot
    team_name = jQuery('#team_' + team_id).text();

    var team_name_pieces = team_name.split(" "); // get the pieces
    team_name_pieces.shift(); // drop the fist item (seed)
    team_name = team_name_pieces.join(" "); // put it back together

    var selector = 'match' + match_id + '_team' + slot;
    var text = '<p id="'+ selector + '" class="slot slot'+ slot +'"><span class="seed">' + seed + '</span> '+ team_name + '</p>';
    jQuery("#" + selector).replaceWith(text);

    // Update the options. This needs work.
    var select = $('#get_team_selector_match' + match_id);
    var selected = (team_id == winner);
    var option = new Option(seed + ' ' + team_name, team_id, selected, selected);
    select.append(option);
}

// NLT, given a match, return the data (winner)
function get_sel_match(id) {
    for (var i = 0; i < sels.length; i++) {
        if (sels[i].match_id == id) return sels[i];
    }
    return {};
}


jQuery(document).ready(
function () {

    $("#tabs").tabs();

    var sel_match;

    sel_match = get_sel_match(15);
    if (sel_match['winner_id']) set_team(62, 1, sel_match.winner_id);

    sel_match = get_sel_match(30);
    if (sel_match['winner_id']) set_team(61, 2, sel_match.winner_id);

    sel_match = get_sel_match(45);
    if (sel_match['winner_id']) set_team(62, 2, sel_match.winner_id);

    sel_match = get_sel_match(60);
    if (sel_match['winner_id']) set_team(61, 1, sel_match.winner_id);

    sel_match = get_sel_match(61);
    if (sel_match['winner_id']) {
        if (sel_match['winner_id']) set_team(63, 1, sel_match.winner_id, sel_match.winner_id);
        jQuery('#get_team_selector_match61').val(sel_match.winner_id);
    }

    sel_match = get_sel_match(62);
    if (sel_match['winner_id']) {
        set_team(63, 2, sel_match.winner_id, sel_match.winner_id);
        jQuery('#get_team_selector_match62').val(sel_match.winner_id);
    }

    sel_match = get_sel_match(63);
    if (sel_match['winner_id']) {
        jQuery('#get_team_selector_match63').val(sel_match.winner_id);
    }
    //end NLT

    //code to fill options the reg-1 round 2 select boxes
    $('#tabs #round2:not(#reg-5 #round2) select').each(function (index) {

        var sid = $(this).attr('id');
        var splits = sid.split('_');
        var match_id = splits[4];
        var t1_div_id = $('#' + match_id + ' p.slot1').attr('id');
        var t2_div_id = $('#' + match_id + ' p.slot2').attr('id');
        var temp = t1_div_id.split('_');
        var t1_id = temp[1];
        temp = t2_div_id.split('_');
        var t2_id = temp[1];

        var str = '<option value="' + t1_id + '">' + $('#' + match_id + ' p.slot1').html() + '</option><option value="' + t2_id + '">' + $('#' + match_id + ' p.slot2').html() + '</option>';
        $(this).append(str);


    });
    //code ends

    //autoselect the values retrieved from DB for round 2
    sels = autoselect(sels);

    //fill options in the round 3 select boxes
    fill_in_the_dropdown($('#round3 select'));

    //autoselect the values retrieved from DB for round 3
    sels = autoselect(sels);


    //fill options in the round 4 select boxes
    fill_in_the_dropdown($('#round4 select'));

    //autoselect the values retrieved from DB for round 4
    sels = autoselect(sels);

    //fill options in the round 5 select boxes
    fill_in_the_dropdown($('#round5 select'));

    //autoselect the values retrieved from DB for round 5
    sels = autoselect(sels);


    //onchange ajax for all select boxes
    $('#tabs select').live('change', function () {
        $('#loader-div').show();

        var prnt = $(this).parent('p').attr('rel');
        var current = $(this);

        var team_id = current.find('option:selected').val();
        var this_match_id = prnt;

            var d = '';

            var match_id_div = $(this).parent('p').parent('div');
            var orig_match_id_p = $(this).parent('p');

            var num_more_rounds = match_id_div.parent('div').nextAll().length;//how many more rounds
            var num_prev_rounds = match_id_div.parent('div').prevAll().length;//how many prev rounds

            for (var m = 0; m < num_more_rounds; m++) {

                var match_id = match_id_div.attr('id');

                //get the current region
                var reg_id_div = match_id_div.parent('div').parent('div').parent('div');
                var reg_id = reg_id_div.attr('id');

                switch (reg_id) {
                    case 'reg-1':
                        var next_round_dd_id = 'get_team_selector_south_' + match_id;
                        break;
                    case 'reg-2':
                        var next_round_dd_id = 'get_team_selector_west_' + match_id;
                        break;
                    case 'reg-3':
                        var next_round_dd_id = 'get_team_selector_east_' + match_id;
                        break;
                    case 'reg-4':
                        var next_round_dd_id = 'get_team_selector_midwest_' + match_id;
                        break;
                }

                var next_round_div = match_id_div.parent('div').next();

                //check if there are more rounds or this is the last round
                if (next_round_div) {
                    var current_selected_value = $(this).val();

                    //find the selected index and apply cases depending on what is the currently selected index
                    var current_selected_index = $(this).prop("selectedIndex");

                    switch (current_selected_index) {
                        case 0:

                            if ($(this).find('option')[1]) {
                                var first_option = $(this).find('option')[1].value;
                                if ($('#' + next_round_dd_id + ' > option[value="' + first_option + '"]').length == 1) {
                                    if ($('#' + next_round_dd_id + ' > option[value="' + first_option + '"]').is(':selected')) {
                                        $.ajax({
                                            type: 'POST',
                                            url: bracketpress_ajax_url,
                                            data:'action=bracketpress&task=save_selection&mid=' + match_id + '&winner=0&bracket=' + post_id,
                                            success:function (res) {
                                            }
                                        });
                                    }
                                    $('#' + next_round_dd_id + ' > option[value="' + first_option + '"]').remove();
                                }
                            }

                            if ($(this).find('option')[2]) {
                                var second_option = $(this).find('option')[2].value;
                                if ($('#' + next_round_dd_id + ' > option[value="' + second_option + '"]').length == 1) {
                                    if ($('#' + next_round_dd_id + ' > option[value="' + second_option + '"]').is(':selected')) {
                                        $.ajax({
                                            type: 'POST',
                                            url: bracketpress_ajax_url,
                                            data:'action=bracketpress&task=save_selection&mid=' + match_id + '&winner=0&banner=' + post_id,
                                            success:function (res) {
                                            }
                                        });
                                    }
                                    $('#' + next_round_dd_id + ' > option[value="' + second_option + '"]').remove();
                                }
                            }
                            break;


                        case 1:
                            if ($(this).find('option')[2]) {
                                var other_option = $(this).find('option')[2].value;
                            }
                            if ($('#' + next_round_dd_id + ' > option[value="' + other_option + '"]').length == 1) {

                                if ($('#' + next_round_dd_id + ' > option[value="' + other_option + '"]').is(':selected')) {

                                    var d = 'task=save_selection&mid=' + match_id;

                                    //do this only for the last matches of each region
                                    if (match_id == 'match15' || match_id == 'match30' || match_id == 'match45' || match_id == 'match60') {
                                        var prev_winner = other_option;
                                        var other_option_index = $('#' + next_round_dd_id + ' > option[value="' + other_option + '"]').index();

                                        switch (other_option_index) {
                                            case 1:
                                                var t1 = current_selected_value;
                                                var t2 = $('#' + next_round_dd_id + ' > option').eq(2).attr('value');
                                                break;

                                            case 2:
                                                var t2 = current_selected_value;
                                                var t1 = $('#' + next_round_dd_id + ' > option').eq(1).attr('value');
                                                break;
                                        }
                                        d += '&t1=' + t1 + '&t2=' + t2 + '&prev_winner=' + prev_winner;

                                    }

                                    $.ajax({
                                        type: 'POST',
                                        url: bracketpress_ajax_url,
                                        data:d + '&action=bracketpress&&winner=' + current_selected_value + '&bracket='+ post_id,
                                        success:function (res) {
                                        }
                                    });
                                    $('#' + next_round_dd_id + ' > option[value="' + other_option + '"]').replaceWith($(this).find('option:selected').clone().attr('selected', 'selected'));

                                }
                                else {
                                    $('#' + next_round_dd_id + ' > option[value="' + other_option + '"]').replaceWith($(this).find('option:selected').clone());
                                }
                            }
                            else if (m == 0 && ($('#' + next_round_dd_id + ' > option[value="' + $(this).find('option')[1].value + '"]').length == 0)) {
                                $('#' + next_round_dd_id).append($(this).find('option:selected').clone());
                            }

                            break;
                        case 2:

                            if ($(this).find('option')[1]) {
                                var other_option = $(this).find('option')[1].value;
                            }
                            if ($('#' + next_round_dd_id + ' > option[value="' + other_option + '"]').length == 1) {
                                if ($('#' + next_round_dd_id + ' > option[value="' + other_option + '"]').is(':selected')) {
                                    var d = 'task=save_selection&mid=' + match_id;
                                    //do this only for the last matches of each region
                                    if (match_id == 'match15' || match_id == 'match30' || match_id == 'match45' || match_id == 'match60') {
                                        var prev_winner = other_option;

                                        var other_option_index = $('#' + next_round_dd_id + ' > option[value="' + other_option + '"]').index();

                                        switch (other_option_index) {
                                            case 1:
                                                var t1 = current_selected_value;
                                                var t2 = $('#' + next_round_dd_id + ' > option').eq(2).attr('value');
                                                break;

                                            case 2:
                                                var t2 = current_selected_value;
                                                var t1 = $('#' + next_round_dd_id + ' > option').eq(1).attr('value');
                                                break;
                                        }
                                        d += '&t1=' + t1 + '&t2=' + t2 + '&prev_winner=' + prev_winner;

                                    }

                                    $.ajax({
                                        type: 'POST',
                                        url: bracketpress_ajax_url,
                                        data:d + '&action=bracketpress&&winner=' + current_selected_value + '&bracket=' + post_id,
                                        success:function (res) {
                                        }
                                    });
                                    $('#' + next_round_dd_id + ' > option[value="' + other_option + '"]').replaceWith($(this).find('option:selected').clone().attr('selected', 'selected'));
                                }
                                else {
                                    $('#' + next_round_dd_id + ' > option[value="' + other_option + '"]').replaceWith($(this).find('option:selected').clone());
                                }
                            }
                            else if (m == 0 && ($('#' + next_round_dd_id + ' > option[value="' + $(this).find('option')[2].value + '"]').length == 0)) {
                                $('#' + next_round_dd_id).append($(this).find('option:selected').clone());
                            }


                            break;
                    }

                }

                $('#' + match_id + ' select').each(function (i, v) {
                    if ($(this).find('option[value=' + current_selected_value + ']').length) {
                        $.ajax({
                            type: 'POST',
                            url: bracketpress_ajax_url,
                            data:'action=bracketpress&task=change_team&bracket=' + post_id + '&mid=' + match_id + '&team_num=' + (i + 1) + '&val=' + current_selected_value,
                            success:function (res) {
                            }
                        });
                    }
                });


                match_id_div = $('#' + next_round_dd_id).parent('p').parent('div');

            }

            if (current_selected_index > 0) {
                for (var n = 0; n < num_prev_rounds - 1; n++) {
                    var match_id = orig_match_id_p.attr('rel');
                    prev_round_div_id = match_id;


                    //check if there are more rounds or this is the last round
                    if (prev_round_div_id) {

                        $('#' + prev_round_div_id + ' select').each(function (i, value) {
                            var t = $(this);
                            var opts = t.children('option');
                            for (var l = 0; l < opts.length; l++) {
                                if (opts[l].value == current_selected_value) {

                                    t.val(opts[l].value);
                                    $.ajax({
                                        type: 'POST',
                                        url: bracketpress_ajax_url,
                                        data:'action=bracketpress&task=save_selection&mid=' + $(this).parent('p').attr('rel') + '&winner=' + opts[l].value + '&bracket=' + post_id,
                                        success:function (res) {
                                        }
                                    });
                                    orig_match_id_p = $(this).parent('p');
                                    return false;
                                }
                            }

                        });

                    }


                }
            }

        var myOpts = this.options;
        if (myOpts.length == '2') {
            var t2 = '';
        }
        else {
            var t2 = myOpts[2].value;
        }

        var match = + $(this).parent('p').attr('rel');
        if (! match) match = this_match_id;

        var data = 'action=bracketpress&task=save_selection&mid=' + match + '&winner=' + $(this).val() + '&bracket=' + post_id;

        $.ajax({
            type: 'POST',
            url: bracketpress_ajax_url,
            data: data,
            success:function (res) {
                $('#loader-div').hide();
            }
        });

        var winner;

        // Save the current winner if we have one
        winner = $('#get_team_selector_match61').val();
        $('#get_team_selector_match61').html('<option value="0">~Select~</option>');       // clear the select box

        // Add the items
        set_team(61, 1, $('#get_team_selector_midwest_match60').val(), winner );
        set_team(61, 2, $('#get_team_selector_west_match30').val(), winner );

        // Save the current winner if we have one
        winner = $('#get_team_selector_match62').val();
        $('#get_team_selector_match62').html('<option value="0">~Select~</option>');       // clear the select box

        // Add the items
        set_team(62, 1, $('#get_team_selector_south_match15').val(), winner );
        set_team(62, 2, $('#get_team_selector_east_match45').val(), winner );

        // Clear and create the final round
        winner = $('#get_team_selector_match63').val();
        $('#get_team_selector_match63').html('<option value="0">~Select~</option>');       // clear the select box

        set_team(63, 1, $('#get_team_selector_match61').val(), winner);
        set_team(63, 2, $('#get_team_selector_match62').val(), winner);

    });
    //onchange code ends
});

})(jQuery);
