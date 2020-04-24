$(document).ready(function() {
	$(".ngspice_send").prop("disabled", false);


	$(".ngspice_send").click( function(){
		var id = $(this).attr("data-id");
		var netlist = $(this).attr("data-netlist");
		var plotlist = $(this).attr("data-plotlist");
		var varconfs = $(this).attr("data-varconfs");
		var rawView = $(this).attr("data-raw");
		var tableView = $(this).attr("data-table");
		var resultConfig = $(this).attr("data-resultconf");
		Content_ngspice.sendContent(id, netlist, plotlist, varconfs, rawView, tableView, resultConfig);
	})

	var Content_ngspice = {
		
		sendContent: function (id, netlist, plotlist, varconfs, rawView, tableView, resultConfig) {

			// disable send button
			$('#' + id + '_send').attr('disabled', 'disabled');
			
			// show loading.gif
			$('#ngspiceResult').empty();
			$('#ngspiceImg').empty();

			varconfs = replaceAll("'", "\"", varconfs);
			varconfs= JSON.parse(varconfs);
			
			str = "?"; // add url params
			
			// for all textfields
			$("#" + id + "_ngspiceForm .ngspice_textfield").each(function() {
				
				var currID = $(this).attr("id");
				var currVal = $(this).val();
				
				// for all varconfigs
				$.each( varconfs, function( index, value ){
						
				
					// if var has configs
					if(currID === "{"+index+"}"){
						
						// for every config tag
						$.each( value, function( conf, confVal ){
							
							confVal =  parseFloat(confVal);
							currVal =  parseFloat(currVal);
						
							// --------------------min ---------------------
							
							if(conf === 'min' && !isNaN(confVal)){

								if( ! (checkMin(currVal, confVal))){
									
									if(typeof value['label'] !== "undefined" && value['label'] !== ""){
										var label = value['label'];
									} else {
										var label = currID;
									}
									
									$('#' + id + '_send').prop('disabled', false);
									alert( mw.message( "loopngspice-error-min-value", label, confVal ) ); //todo msg
									$('#ngspiceResult').empty();
									$('#ngspiceImg').empty();
									throw new Error( mw.message( "loopngspice-error-min-value", label, confVal ) );//todo msg
								}
									
							}
							
							// --------------------min ---------------------
							
							// --------------------max ---------------------
						
							if(conf === 'max' && !isNaN(confVal)){
								
								if( ! (checkMax(currVal, confVal))){
									
									if(typeof value['label'] !== "undefined" && value['label'] !== ""){
										var label = value['label'];
									} else {
										var label = currID;
									}
									$('#' + id + '_send').prop('disabled', false);
									alert( mw.message( "loopngspice-error-max-value", label, confVal ) );
									$("#ngspiceResult").empty();
									$('#ngspiceImg').empty();
									throw new Error( mw.message( "loopngspice-error-max-value", label, confVal ) );
								} 
								
							}
						
							// --------------------max ---------------------
							
						});
					} // if var has configs
					
				});
				
				// get inputs with values instead of vars
				netlist = netlist.replace(new RegExp(currID,"gm"),currVal);
				plotlist = plotlist.replace(new RegExp(currID,"gm"),currVal);
				str = str + currID +"="+ currVal + "&";
			});
			
			
			
			str = str.substr(0, str.length-1);
			
			sendToPHPExec(id, str, netlist, plotlist, rawView, tableView, resultConfig);
		}
	};
	
	window.Content_ngspice = Content_ngspice;


	function replaceAll(find, replace, str) {
		return str.replace(new RegExp(find, 'g'), replace);
	}

	function checkMin(v1, v2){
		return v1 >= v2; 
	}

	function checkMax(v1, v2){
		return v1 <= v2;
	}

	function sendToPHPExec(id, params, netlist, plotlist, rawView, tableView, resultConfig){
		
		// if parameters with vars
		if(params !== ''){
			params = params + "&netlist=" + netlist +"&plotlist="+plotlist+"&rawView="+rawView+"&tableView="+tableView+"&id="+id+"&resultConfig="+resultConfig;
		// if just netlist and plotlist without vars
		} else {
			params = "?netlist=" + netlist +"&plotlist="+plotlist+"&rawView="+rawView+"&tableView="+tableView+"&id="+id+"&resultConfig="+resultConfig;
		}

		id = $.trim(id);
		
		var xmlhttp;
		
		if (window.XMLHttpRequest) {
			
			xmlhttp=new XMLHttpRequest();
			
		} else {
			
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		
		}
		
		xmlhttp.onreadystatechange=function(){
			
			
			if (xmlhttp.readyState==4 && xmlhttp.status==200)    {
				$('#' + id + '_send').prop('disabled', false);
				$('#ngspiceResult').empty();
				$('#ngspiceImg').empty();
				
				ngspice_result = xmlhttp.response;
				
				$('#ngspiceResult').html(ngspice_result);
			}
			
		}
		
		xmlhttp.open("POST", "https://" + window.location.host + "/loop/Special:LoopNgSpice" + params, true);
		xmlhttp.send();
		
	}

})