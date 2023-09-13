$(document).ready(function () {
  $("#clientselection").multiselect();

  var select = document.getElementById("action");
  var selectT = document.getElementById("trigger");
  displayInput(select.value, "ban", "bantime");
  displayInput2(selectT.value, "client", "clientselectiond", "threshold");

  var handleDataTableButtons = function () {
    if ($("#rules").length) {
      $("#rules").DataTable({
        dom: '<"dataTables_exportbtn"B>ft',
        paging: false,
        order: [[0, "asc"]],
        bInfo: false,
        buttons: [
          {
            text: "Export",
            extend: "csv",
            className: "btn-sm btn-dark",
            exportOptions: {
              columns: [0, 3, 4, 5],
            },
          },
        ],
        responsive: true,
      });
    }
  };

  TableManageButtons = (function () {
    "use strict";
    return {
      init: function () {
        handleDataTableButtons();
      },
    };
  })();

  TableManageButtons.init();
});
