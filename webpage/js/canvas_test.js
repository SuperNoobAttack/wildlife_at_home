var species = [];
var options = "";

var newRectCallback = function(id) {
    table = "<div class='panel panel-primary' id='S" + id + "'>" +
        "<div class='panel-heading'>" +
            "Selection " + id +
			"<button type='button' class='close' data-dismiss='modal' aria-hidden='true' id='remove" + id + "'>X</button>" +
        "</div>" +
        "<div class='panel-body'>" +
            "<select class='form-control' id='species" + id + "'>" + options + "</select>" +
            "<div class='input-group'>" +
                "<input type='text' class='form-control disabled' value='On Nest?' disabled>" +
                "<span class='input-group-addon'>" +
                    "<input type='checkbox' id='nest" + id + "'>" +
                "</span>" +
            "</div>" +
            "<div class='input-group hidden' id='otherdiv" + id + "'>" +
                "<div class='input-group'>" +
                    "<span class='input-group-addon'>" +
                        "Species:" +
                    "</span>" +
                    "<input id='other" + id + "' type='text' class='form-control' placeholder='Species name'>" +
                "</div>" +
            "</div>" +
        "</div>" +
    "</div>";

    $("#selection-information").append(table);
    $("#submit-selections-button").prop("disabled", false);
    $("#submit-selections-button").removeClass("disabled");
    $("#nothing-here-button").prop("disabled", true);
    $("#nothing-here-button").addClass("disabled");

    $("#species" + id).change(function (e) {
        var selected = $("#species" + id + " option:selected");
        if (selected.text() == "Other") {
            $("#other" + id).val("");
            $("#otherdiv" + id).removeClass('hidden');
        } else {
            $("#otherdiv" + id).addClass('hidden');
        }
    });

    $("#remove" + id).click(function (e) {
        cs.removeRect(cs, e.target.id.substring(6));
    });
};

var deleteCallback = function(id, empty) {
    var selectionId = 'S' + id;
    $('#'+selectionId).remove();

    if (empty) {
        $("#submit-selections-button").prop("disabled", true);
        $("#submit-selections-button").addClass("disabled");
        $("#nothing-here-button").prop("disabled", false);
        $("#nothing-here-button").removeClass("disabled");
    }
};

var img = new Image();
img.src = imgsrc;
var cs = new canvasSelector($("#canvas"), img, {
    "logging": true,
    "callback": newRectCallback,
    "deleteCallback": deleteCallback
});

img.onload = function() {
    cs.resizeFunc(cs);
};

$(document).ready(function() {
    $.ajax({
        url: "http://csgrid.org/csg/wildlife_mmattingly/canvas_select.php",
        async: false,
        dataType: 'json',
        success: function(data) {
            species = data;
            species.forEach(function(e) {
                options += "<option value='" + e.id + "'>" + e.name + "</option>";
            });
        }
    });

    $('#submitForm').submit(function(e) {
        // add the info for each rectangle
        var boxes = [];
        cs.rectangles.forEach(function(e) {
            var species = $("#species" + e.id + " option:selected")[0].value;
            var nest = $("#nest" + e.id)[0].checked ? 1 : 0;

            boxes.push({
                'x': e.left,
                'y': e.top,
                'width': e.width,
                'height': e.height,
                'species_id': species,
                'on_nest': nest
            });
        });

        var formData = {
            'metadata': {
                'image_id' : $('input[name=image_id]').val(),
                'comments' : '',
                'start_time': $('input[name=submitStart]').val(),
                'nothing_here': cs.rectangles.length == 0 ? 1 : 0
            },
            'boxes': boxes
        };

        console.log(formData);

        // process
        $.ajax({
            type: 'POST',
            url: 'canvas_submission.php',
            data: formData,
            dataType: 'json',
            encode: true
        })
            .done(function(data) {
                if (!data['success']) {
                    $("#ajaxalert").removeClass('alert-success');
                    $("#ajaxalert").addClass('alert-danger');
                    $("#ajaxalert").html("<strong>Error!</strong> " + data['errors']);
                    $("#ajaxalert").removeClass('hidden');
                } else {
                    $("#ajaxalert").removeClass('alert-danger');
                    $("#ajaxalert").addClass('alert-success');

                    var html = '';
                    if (data['count'] == 0) html = 'Nothing here submitted.';
                    else if (data['count'] == 1) html = '1 rectangle added.';
                    else html = data['count'] + ' rectangles added.';

                    $("#ajaxalert").html("<strong>Success!</strong> " + html + ' Reloading page in 3 seconds.');
                    $("#ajaxalert").removeClass('hidden');

                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                }
            });

            //.fail(function(data) {
            //    console.log(data);
            //});
        
        // stop normal submission
        e.preventDefault();
    });
});

$("#discuss-button").click(function() {
    $("#forumContent").val('I would like to discuss the following trail cam image:\n\n[img]' + imgsrc + '[/img]');
    $("#forumPost").submit();
});

$("#skip-button").click(function() {
    location.reload();
});

$("#nothing-here-button").click(function() {
    $("#submitForm").submit();
});

$("#submit-selections-button").click(function() {
    $("#submitForm").submit();
});

$(window).resize(function() {
    cs.resizeFunc(cs);
});
