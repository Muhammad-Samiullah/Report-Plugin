function getStaffData() {
	jQuery(document).ready(function($) {
		$.ajax({
			url: frontend_ajax_object.ajaxurl,
			type: 'post',
			data: {
				'action':'data_getter_hospital'
			},
			success: function( response ) {
				const obj = JSON.parse(response);
				console.log(obj);
				let staffData = new Object();
				let pathologist_name, technician_name, consultant_name;
				pathologist_name = obj[1].text;
				consultant_name = obj[3].text;
				technician_name = obj[12].text;
				jQuery("#consultant-name").html(consultant_name);
				jQuery("#technician-name").html(technician_name);
				jQuery("#pathologist-name").html(pathologist_name);
			},
			error: function( response ) {
				console.log(response);
			}
		});
	});
}

jQuery(document).ready(function($){
jQuery('#hospital-form').on('submit', function(e){

	jQuery("#success_msg").css("display","none");
	jQuery("#error_msg").css("display","none");
	var form_data = jQuery( this ).serializeArray();
	e.preventDefault();

   jQuery.ajax({
      url: frontend_ajax_object.ajaxurl,
      type:"POST",
      dataType:'text',
      data : form_data,
	  success: function(response){
        $("#success_msg").css("display","block");
		  console.log(response);
		  
     }, error: function(data){
         $("#error_msg").css("display","block");      }
   });
  });

	
	jQuery(document).on('click', '#see-patient-reports-btn', function(e){
		var id = $(this).attr("data-id");
		var action = $(this).attr("data-action");
		var time_stamp = $(this).attr("data-time");
		var form_data = {'patient-id': id, 'time-stamp': time_stamp, 'action': action};
		jQuery("#see-patient-reports-div").css("display","block");
		console.log(form_data);

	   $.ajax({
		  url: frontend_ajax_object.ajaxurl,
		  type:"POST",
		  dataType:'text',
		  data : form_data,
		  success: function(response){
			  jQuery("see-patient-reports-div").empty();
			  let content = "";
			  jQuery("#see-patient-reports-div").empty();
			  console.log(response);
			  const obj = JSON.parse(response);
			  if(Object.keys(obj).length > 0) {
				  content += "<center><h2>Reports Found</h2></center>";
					for (var key in obj) {
						if (obj.hasOwnProperty(key)) {
							content += '<br><br>';
							content += '<span><h5>Panel Name: </h5>' + obj[key]['panel_name'] + '</span>';
							content += '<div style="display: flex; justify-content: space-between">'
							content += '<h6>Date: ' + obj[key]['time_stamp'] + '</h6>';
							content += '<button class="btn btn-primary" id="see-report-btn" data-report-id="'+obj[key]['id']+'" data-patient-id="'+obj[key]['patient_id']+'" data-action="see_report" data-panel="'+obj[key]['panel_name']+'" data-time="'+obj[key]['time_stamp']+'">See Report</button>';
							content += '</div>';
							content += '<br>';
							content += '<hr>';
						}
					}
			  }
			  else {
				  content += "<br>No Reports Found!";
			  }
			  jQuery("#see-patient-reports-div").append(content);
		 }, error: function(data){
			 let content = "Error Fetching Reports";
			 jQuery("#see-patient-reports-div").append(content);
		 }
	   });
  });
	
	
	
	jQuery(document).on('click', '#see-report-btn', function(e){
		jQuery("#patient-container").css('display', 'none');
		jQuery("#patient-report").css('display', 'block');
		var report_id = $(this).attr("data-report-id");
		var patient_id = $(this).attr("data-patient-id");
		var panel_name = $(this).attr("data-panel");
		var time_stamp = $(this).attr("data-time");
		var action = $(this).attr("data-action");
		var form_data = {'patient-id': patient_id, 'report-id': report_id, 'panel-name': panel_name, 'time-stamp': time_stamp, 'action': action};
		console.log(form_data);

	   $.ajax({
		  url: frontend_ajax_object.ajaxurl,
		  type:"POST",
		  dataType:'text',
		  data : form_data,
		  success: function(response){
			  const obj = JSON.parse(response);
			  console.log("Object Start");
			  console.log(obj);
			  console.log("Object End");
			  let content = "";
			  content += "<h2 style='text-align: center; text-decoration: underline'>Hospital Name</h2>";
			  content += "<h5 style='text-decoration: underline; margin: 30px 0px'>Date: "+ obj[3]["time_stamp"] +"</h5>";
			  content += "<div style='display: flex; justify-content: space-between'>";
			  content += "<h5 style='text-decoration: underline'>Patient Name: "+ obj[3]['new-patient-name'] +"</h5>";
			  content += "<h5 style='text-decoration: underline'>Patient Disease: "+ obj[3]['new-patient-disease'] +"</h5>";
			  content += "</div>";
			  content += "<h5 style='text-decoration: underline; margin-top: 30px'>Report Group: "+ obj[3]['panel_name'] +"</h5>";
			  content += "<table style='margin-top: 50px'>";
			  content += '<tr>';
			  content += '<th style="background-color: grey; text-align: center">Name</th>';
			  content += '<th style="background-color: grey; text-align: center">Lower Range</th>';
			  content += '<th style="background-color: grey; text-align: center">Value</th>';
			  content += '<th style="background-color: grey; text-align: center">Result</th>';
			  content += '</tr>';
				for (var key in obj) {
					if (obj.hasOwnProperty(key) && obj[key]['name'] != 'action' && obj[key]['name'] != 'panel_name') {
						content += '<tr>';
						content += '<td style="text-align: center">'+ obj[key]['name'] +'</td>';
						content += '<td style="text-align: center">'+ obj[key]['lower_range'] +'</td>';
						content += '<td style="text-align: center">'+ obj[key]['value'] +'</td>';
						if(parseInt(obj[key]['value']) < parseInt(obj[key]['lower_range'])) {
							content += '<td style="text-align: center;"><b style="color: red;">Resistance</b></td>';
						}
						else {
							content += '<td style="text-align: center">Normal</td>';
						}
						content += '</tr>';
					}
				}
			  content += '</table>';
			  content += "<div style='display: flex; justify-content: space-around; margin-top: 150px'>";
			  content += "<div>";
			  getStaffData();
			  content += '<h5 style="border-top: 2px solid black; padding-top: 5px">Consultant</h5>';
			  content += '<h5 style="padding-top: 5px" id="consultant-name"></h5>';
			  content += "</div>";
			  content += "<div>";
			  content += '<h5 style="border-top: 2px solid black; padding-top: 5px">Pathologist</h5>';
			  content += '<h5 style="padding-top: 5px" id="pathologist-name"></h5>';
			  content += '</div>';
			  content += "<div>";
			  content += '<h5 style="border-top: 2px solid black; padding-top: 5px">Technician</h5>';
			  content += '<h5 style="padding-top: 5px" id="technician-name"></h5>';
			  content += "</div>";
			  content += "</div>";
			  jQuery("#patient-report").append(content);
			  
			  
			  
		 }, error: function(data){
			 alert('Error');
		 }
	   });
  });
	getStaffData();
	
jQuery('#new-patient-form').on('submit', function(e){

	jQuery("#success_msg").css("display","none");
	jQuery("#error_msg").css("display","none");
	var form_data = jQuery( this ).serializeArray();
	e.preventDefault();

   jQuery.ajax({
      url: frontend_ajax_object.ajaxurl,
      type:"POST",
      dataType:'text',
      data : form_data,
	  success: function(response){
        $("#success_msg").css("display","block");
		  console.log(response);
		  jQuery('#new-patient-form').trigger("reset");
     }, error: function(data){
         $("#error_msg").css("display","block");      }
   });
  });
	
	jQuery('#find-patient-form').on('submit', function(e){
	var form_data = jQuery( this ).serializeArray();
	e.preventDefault();
	let hasValidatedName = true;
	if(form_data[0].value.length <= 2 && form_data[1].value.length == 0) {
		hasValidatedName = false;
	}
   jQuery.ajax({
      url: frontend_ajax_object.ajaxurl,
      type:"POST",
      dataType:'text',
      data : form_data,
	  success: function(response){
		  const obj = JSON.parse(response);
		  console.log(obj);
		  jQuery("#success_msg").css('display', 'block');
		  jQuery("#success_msg").empty();
		  let content = "";
		  let report_id;
		  if(Object.keys(obj).length > 0 && hasValidatedName) {
			  content += "<h3>Patient Information</h3>";
			  for (key in obj) {
				  if (obj.hasOwnProperty(key)) {
					  report_id = obj[key]['id'];
					  let url = "https://smart.taleemkahani.com/reports/?id=c";
					  url += report_id;
					  content += "<h5>Patient ID: "+ obj[key]['id'] +"</h5>";
					  content += "<h5>Patient Name: "+ obj[key]['new-patient-name'] +"</h5>";
					  content += "<h5>Patient Disease: "+ obj[key]['new-patient-disease'] +"</h5>";
					  content += "<h5>Patient Residence: "+ obj[key]['new-patient-residence'] +"</h5>";
					  content += "<div style='display: flex; justify-content: space-around; margin-top: 20px'>";
					  content += '<button id="see-patient-reports-btn" class="btn btn-primary" data-id="'+obj[key]['id']+'" data-action="see_reports_handler">Search Reports</button>';
					  content += '<a href="'+url+'" id="add-patient-report-btn" class="btn btn-primary">Add Report</a>';
					  content += "</div>";
					  content += '<div id="see-patient-reports-div" style="display: none"></div>';
				  }
				}
			  console.log(content);
		  }
		  else {
			  if(!hasValidatedName) {
				  content += "Invalid Patient Name OR ID<br>";
			  }
			  content += "No Patient Found!";
		  }
		  jQuery("#success_msg").append(content);
     }, error: function(data){
         console.log(data);      
		 alert('No Patient Found');
		 jQuery("#error_msg").css('display', 'block');
	 }
   });
  });
	
	var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
    return false;
};
	
	
	jQuery('#report-form').on('submit', function(e){
	e.preventDefault();
	let isFormSubmitted = false;
// 	jQuery("#panel_select").css("display","none");
	var form_data = jQuery( this ).serializeArray();
		if(form_data.length > 2) {
			isFormSubmitted = true;
		}

   jQuery.ajax({
      url: frontend_ajax_object.ajaxurl,
      type:"POST",
      dataType:'text',
      data : form_data,
	  success: function(response){
		  if(!isFormSubmitted) {
			  var id = getUrlParameter('id');
			  id = id.substr(1, id.length);
			  jQuery("#panel_select").remove();
		  	console.log(response);
		  	const obj = JSON.parse(response);
			console.log(obj);
			  let content = "";
			  content += '<input type="hidden" class="form-control" name="patient-id" id="patient-id-panel-input" placeholder="Patient ID" required/>';
			  content += "<input type='hidden' value='" + obj[1]['panels'] + "' name='panel_name' />";
			for (key in obj) {
			  if (obj.hasOwnProperty(key) && key != 0) {
				  content += '<strong><Label></Label>' + obj[key]['name'] + '</strong><br><br>';
				  content += '<input type="number" class="form-control" name="' + obj[key]['abreviation'] + '" placeholder="Value" required/><br><br>';
			  }
			}
		  content += '<input name="report-btn" type="submit" id="report-btn" value="Submit Report" class="btn btn-primary" />';
			  jQuery("#report-inputs").css("display","block");
			  jQuery("#report-inputs").append(content);
			  jQuery("#patient-id-panel-input").val(id);
		  }
		  else {
			jQuery("#report-inputs").css('display', 'none');
			  jQuery("#panel_success").css('display', 'block');  
		  }
     }, error: function(data){
         jQuery("#panel_error").css("display","block");      
	 }
   });
  });
	
	
	
	
});

function getHospitalData() {
	jQuery(document).ready(function($) {
		$.ajax({
			url: frontend_ajax_object.ajaxurl,
			type: 'post',
			data: {
				'action':'data_getter_hospital'
			},
			success: function( response ) {
				console.log(response);
				const obj = JSON.parse(response);
				jQuery("#hospital-name").val(obj[14].text);
				jQuery("#hospital-address").val(obj[13].text);
				jQuery("#hospital-city").val(obj[12].text);
				jQuery("#focal-person").val(obj[11].text);
				jQuery("#focal-person-designation").val(obj[9].text);
				jQuery("#focal-person-contact").val(obj[10].text);
				jQuery("#focal-person-email").val(obj[7].text);
				jQuery("#consultant-name").val(obj[8].text);
				jQuery("#consultant-qualification").val(obj[0].text);
				jQuery("#consultant-designation").val(obj[10].text);
				jQuery("#pathologist-name").val(obj[4].text);
				jQuery("#pathologist-qualification").val(obj[5].text);
				jQuery("#pathologist-designation").val(obj[3].text);
				jQuery("#technician-name").val(obj[1].text);
				jQuery("#technician-qualification").val(obj[2].text);
				jQuery("#technician-designation").val(obj[0].text);
			},
			error: function( response ) {
				console.log(response);
			}
		});
	});	
}

if(window.location.href == "https://smart.taleemkahani.com/hospital/") {
	getHospitalData();
}

jQuery(document).ready(function($) {
    $.ajax({
        url: frontend_ajax_object.ajaxurl,
        type: 'post',
        data: {
            'action':'data_getter_panels'
        },
        success: function( response ) {
			const obj = JSON.parse(response);
			console.log(obj);
			let options = "";
			for (key in obj) {
			  if (obj.hasOwnProperty(key) && key != 0) {
				  options += "<option value='" + obj[key]['panels'] + "'>" + obj[key]['panels'] + "</option>";
			  }
			}
			jQuery("#panel_select").append(options);
        },
		error: function( response ) {
            console.log(response);
        }
    });
});


getStaffData();

jQuery(document).ready(function(){
	jQuery('#panel_select').on('change', function(e){
		jQuery("#report-form").submit();
  });
})

function checkColor(element, lower_range, upper_range) {
      if(element.value != '') {
        if(element.value < lower_range || element.value > upper_range) {
          element.style.backgroundColor = "red";
        }
        else {
          element.style.backgroundColor = "white";
        }
      }
      else {
        element.style.backgroundColor = "white";
      }
}

