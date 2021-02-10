/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
window.onload = iniciar;

var botones_seleccionados = [];

function iniciar() {
    $(document).on("click", "#bt_confirmar", function () {
        for (var i = 0; i < botones_seleccionados.length; i++) {
            var button = botones_seleccionados[i];
            crear_reserva(button);
        }
    });

    $(document).on("click", ".btn-res", function () {
        $(this).attr("class", "btn-res-selected");
        botones_seleccionados.push($(this));
    });

    $(document).on("click", ".btn-res-selected", function () {
        $(this).attr("class", "btn-res");

        var removeItem = "btn-res";
        botones_seleccionados = jQuery.grep(botones_seleccionados, function (value) {
            return value.attr("class") != removeItem;
        });
    });

}


function crear_reserva(button) {
    var hora = button.attr('data-hora');
    var pista = button.attr('data-pista');
    var fecha = button.attr('data-fecha');
    $.ajax({
        url: "reserva_new",
        method: 'POST',
        dataType: 'json',
        data: {
            'fecha': fecha,
            'hora': hora,
            'pista': pista
        },
        success: function (response) {
            if (response.resultado) {
                button.parent().attr("class", "si-ocup");
                button.replaceWith("<p class=\"btn-ocup\">OCUPADO</p>");
            } else {
                alert("YA EXISTE UNA RESERVA A ESA HORA");
            }
        },
        error: function (xhr, status, error) {
            alert("ERROR AL CREAR LA RESERVA");
        }
    });
}
