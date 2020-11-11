function loadCalibrationGrid(userSeq,folderSeq){
        var source =
        {
            datatype: "json",
            pagesize: 20,
            sortcolumn: 'startedon',
            sortdirection: 'desc',
            datafields: [
                { name: 'seq', type: 'integer' },
                { name: 'caltype', type: 'string' },
                { name: 'startedon', type: 'date' },
                { name: 'action', type: 'string' },
            ],
            url: 'showCalibrationHistory.php?call=getCalibrationHistory&userSeq='+userSeq+'&folderSeq='+folderSeq,
            root: 'Rows',
            cache: false,
            beforeprocessing: function(data)
            {
                source.totalrecords = data.TotalRows;
            },
            filter: function()
            {
                // update the grid and send a request to the server.
                $("#jqxgrid").jqxGrid('updatebounddata', 'filter');
            },
            sort: function()
            {
                    // update the grid and send a request to the server.
                    $("#jqxgrid").jqxGrid('updatebounddata', 'sort');
            },
        };
       
        var dataAdapter = new $.jqx.dataAdapter(source);
        var actions = function (row, columnfield, value, defaulthtml, columnproperties) {
            data = $('#jqxgrid').jqxGrid('getrowdata', row);
            var html = "<div style='text-align: center; margin-top: 5px;'><a href='javascript:showDetail("+ data['seq'] + ",\""+ data['caltype']  +"\")'><i class='fa fa-building-o' title='Show Calibration Detail'></i></a>";
                html += "</div>";
            
            return html;
        }
        var columnrenderer = function (value) {
            return '<div style="text-align: center; margin-top: 5px;">' + value + '</div>';       
    }
        $("#jqxgrid").jqxGrid(
        {
            width: "100%",
            source: dataAdapter,                
            pageable: true,
            autoheight: true,
            showtoolbar: false,
            sortable: true,
            filterable: true,
            columnsresize: true,
            altrows: true,
            enabletooltips: true,
            altrows: true, 
            theme: "energyblue",
            virtualmode: true,
            rendergridrows: function()
            {
                  return dataAdapter.records;     
            },               
            columns: [
              { text: 'id', datafield: 'seq' , hidden:true},
              { text: 'Started On',  datafield: 'startedon',cellsformat: 'dd-MM-yyyy hh:mm:ss tt'},
              { text: 'Calibration Type', datafield: 'caltype'},
              { text: 'Actions', datafield: 'action',cellsrenderer:actions,renderer:columnrenderer},
              
            ]
        });
    }  