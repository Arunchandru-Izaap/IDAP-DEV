          </div>
    <p class="copy-center copyright">© Copyright {{ date('Y') }} Sun Pharma. All rights reserved.</p>
  </div>
</div>
</div>
</section>

  <!-- The Modal -->
  <div class="modal show" id="myModal">
    <div class="modal-dialog modal-dialog-centered model-pop-ct">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header border-0">
            <div class="tich-logo">
            <img src="{{ asset('admin/images/poptick.svg') }}">
            </div>
          <button type="button" class="close" data-dismiss="modal">
              <img src="{{ asset('admin/images/close.svg') }}">
          </button>
        </div>
        <!-- Modal body -->
        <div class="modal-body">
          <p>VQ Request Initiated Successfully</p>
          <div class="dymdate">
              <h5>Start Date</h5>
              <h6 id="startval"></h6>
          </div>
          <div class="dymdate">
              <h5>End Date</h5>
              <h6 id="enddateval"></h6>
          </div>

          <a class="btn orange-btn" href="{{url('initiator/listing')}}">VIEW VQ LIST</a>

        </div>
        
        
      </div>
    </div>
  </div>

  <div class="modal show" id="warningpdms">
    <div class="modal-dialog modal-dialog-centered model-pop-ct">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header border-0">
            <div class="tich-logo">
            <img src="{{ asset('admin/images/poptick.svg') }}">
            </div>
          <button type="button" class="close" data-dismiss="modal">
              <img src="{{ asset('admin/images/close.svg') }}">
          </button>
        </div>
        <!-- Modal body -->
        <div class="modal-body">
          <p>You have exceeded PDMS discount cap</p>
          

          <a class="btn orange-btn" class="close" data-dismiss="modal">OK</a>

        </div>
        
        
      </div>
    </div>
  </div>

  <div class="modal show" id="warningmaxcap">
    <div class="modal-dialog modal-dialog-centered model-pop-ct">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header border-0">
            <div class="tich-logo">
            <img src="{{ asset('admin/images/poptick.svg') }}">
            </div>
          <button type="button" class="close" data-dismiss="modal">
              <img src="{{ asset('admin/images/close.svg') }}">
          </button>
        </div>
        <!-- Modal body -->
        <div class="modal-body">
          <p>You have exceeded Max discount cap</p>
          

          <a class="btn orange-btn" class="close" data-dismiss="modal">OK</a>

        </div>
        
        
      </div>
    </div>
  </div>
<script type="text/javascript">
$(document).ready(function() {
	
// 	$('.dt-checkboxes-cell .dt-checkboxes').after('<span class="checkmarktitle"></span>');
  window.addEventListener( "pageshow", function ( event ) {
  var historyTraversal = event.persisted || ( typeof window.performance != "undefined" && window.performance.navigation.type === 2 );
  if ( historyTraversal ) {
    // Handle page restore.
    //alert('refresh');
    window.location.reload();
  }
});
   size_li = $("#myList li").length;
    x=3;
    $('#myList li:lt('+x+')').show();
    $('#loadMore').click(function () {
        x= (x+5 <= size_li) ? x+5 : size_li;
        $('#myList li:lt('+x+')').show();
         $('#showLess').show();
        if(x == size_li){
            $('#loadMore').hide();
        }
    });
// $(function () {
//     $("#startdate").datepicker({
//         numberOfMonths: 2,
//         onSelect: function (selected) {
//             var dt = new Date(selected);
//             dt.setDate(dt.getDate() + 1);
//             $("#startdate").datepicker("option", "minDate", dt);
//         }
//     });
//     $("#enddate").datepicker({
//         numberOfMonths: 2,
//         onSelect: function (selected) {
//             var dt = new Date(selected);
//             dt.setDate(dt.getDate() - 1);
//             $("#enddate").datepicker("option", "maxDate", dt);
//         }
//     });
// });

// $( function() {


/*
  var todaydt = new Date();
    $("#startdate").datepicker({
                autoclose: true,
                    dateFormat: "mm/dd/yy",
                    endDate: todaydt,
                onSelect: function (date) {
                   //Get selected date 
                    var date2 = $('#startdate').datepicker('getDate');
                    //sets minDate to txt_date_to
                    $('#enddate').datepicker('option', 'minDate', date2);
                }
            });
            $('#enddate').datepicker({
                dateFormat: "mm/dd/yy",
                
            });
*/
/*
        var today, datepicker;
        today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
        datepicker = $('#startdate').datepicker({
            minDate: today,
            format: 'yyyy-mm-dd'
        });
*/


var todaydt = new Date();
			$("#startdate").datepicker({
			    dateFormat: 'dd/mm/yy',
			    minDate:0,
			    onSelect: function (selectedDate) {
			        if (this.id == 'startdate') {
			           var arr = selectedDate.split("/");
                       var date = new Date(arr[2]+"-"+arr[1]+"-"+arr[0]);
                       var d = date.getDate();
                       var m = date.getMonth();
                       var y = date.getFullYear();
                       if(m >= 3){
                         y = y + 1;
                       }
                       var minDate = new Date("03/31/"+y);
                       var startDate = "{{  $start_date ?? '' }}";
                       var endDate = "{{ $end_date ?? '' }}";
                       // var minDate = new Date(y, m, d + 364);
                       if(startDate) {
                         $("#enddate").datepicker('setDate', endDate);
                         $("#startdate").datepicker('setDate',startDate);
                       }else {
                          $("#enddate").datepicker('setDate', minDate);
                       }
                       $('#initiate').removeClass( "d-none" )
			        }
			    }
			});
			$("#enddate").datepicker(
        {
        dateFormat: 'dd/mm/yy' ,
        onSelect: function (selectedDate) {
          var endDate = "{{ $end_date ?? '' }}";
          if(endDate) {
            $("#enddate").datepicker('setDate', endDate);
          }
        },
      });



// } );

  
	$(function() {
	//     $( "#startdate" ).datepicker();
	//     $( "#enddate" ).datepicker();
      $( "#startdate_re" ).datepicker({
			    dateFormat: 'dd/mm/yy',
			    minDate:0,
			    onSelect: function (selectedDate) {
			        if (this.id == 'startdate_re') {
			            var arr = selectedDate.split("/");
			            var date = new Date(arr[2]+"-"+arr[1]+"-"+arr[0]);
			            var d = date.getDate();
			            var m = date.getMonth();
			            var y = date.getFullYear();
                        if(m >= 3){
                          y = y + 1;
                        }
			            var minDate = new Date("03/31/"+y);
			            // var minDate = new Date(y, m, d + 364);
			            $("#enddate_re").datepicker('setDate', minDate);
			            $('#re-initiate').removeClass( "d-none" )
			
			        }
			    }
			});
	    $( "#enddate_re" ).datepicker({dateFormat: 'dd/mm/yy'});

      $( "#new_counter_startdate" ).datepicker({
			    dateFormat: 'dd/mm/yy',
			    minDate:0,
			    onSelect: function (selectedDate) {
			        if (this.id == 'new_counter_startdate') {
                     var arr = selectedDate.split("/");
                     var date = new Date(arr[2]+"-"+arr[1]+"-"+arr[0]);
                     var d = date.getDate();
                     var m = date.getMonth();
                     var y = date.getFullYear();
                     if(m >= 3){
                       y = y + 1;
                     }
                     var minDate = new Date("03/31/"+y);
                     // var minDate = new Date(y, m, d + 364);
			            $("#new_counter_enddate").datepicker('setDate', minDate);

                  let new_institutes_drop = $("#new_institutes_drop").val();
                  if(new_institutes_drop.length > 0){
                    $('#add_new_counter_btn').show();
                  }
			        }
			    }
			});
	    $( "#new_counter_enddate" ).datepicker({dateFormat: 'dd/mm/yy'});
	});


	$('.showsingle').click(function() {
	    $('.targetDiv').hide();
	    $('.showsingle').removeClass("active");
	    $(this).addClass("active");
	    $('#section' + $(this).attr('target')).show();
	});
	if (!Cookies.get("api-key")){
		$.ajax({
			url: "/generate_token",
			type: 'GET',  // http method
			success: function(result){
				Cookies.set('api-key', result);
			}
		});
 	}

});

$("#frminvet").submit(function(e) {
  e.preventDefault();
  $('#startval').empty();//added for empty the dates
  $('#enddateval').empty();//added for empty the dates
  var startdate = moment($("#startdate").val(),'DD/MM/YYYY').format('MM/DD/YYYY');
  var enddate =moment($("#enddate").val(),'DD/MM/YYYY').format('MM/DD/YYYY');
$('#startval').append($("#startdate").val());//changed the date format
$('#enddateval').append($("#enddate").val());//changed the date format

  if(startdate == "" || enddate == "" ) {
    $("#error_message").show().html("Start Date and End Date are Required");
  } else {
    $("#error_message").html("").hide();
    // $('#myModal').modal('show');
      $.ajax({
        type: "get",
        url: "/initiator/createVQ/",
        "headers": {
      "Authorization": "Bearer " + Cookies.get("api-key")
  },
        data: "from="+startdate+"&to="+enddate,
        success: function(data){
          $('#myModal').modal('show');
        }
      });
  }
});

  $('.fromdate').hide();
  $('.radio-label-ct').click(function() {
      $('.fromdate').hide();
      $('#frm' + $(this).attr('target')).show();
      if($(this).attr('target') == 'radioopt'){
        if($('.radio-add').is(':checked')) {
          $('#frm' + $('.radio-add').attr('target')).show();
        }
      }
  });

  $('.radio-add').click(function(){
    $('.formadd').hide();
    $('#frm' + $(this).attr('target')).show();
  })

  $('.disable-click').off('click');


$(".table-ct .dataTables_filter label").append("<img src='{{ asset('admin/images/search.svg') }}' class='search-ct' alt=''>");
$('.dataTables_filter label').contents().filter((_, el) => el.nodeType === 3).remove();
$(".dataTables_filter label input").keyup(function(){
        $(".search-ct").css("opacity", "0");
});


//var activeurl = window.location;
//$('.list-group-flush ul li').removeClass("active");
//$('a[href="'+activeurl+'"]').parent('li').addClass('active');

// $('.dataTables_paginate .pagination .previous a, .dataTables_paginate .pagination .next a').contents().filter((_, el) => el.nodeType === 3).remove();
// $(".dataTables_paginate .pagination .previous a").append("<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>");
// $(".dataTables_paginate .pagination .next a").append("<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>");

  // var serch = $('.dataTables_filter label input').val();

  //   if(serch == "" ) {
  //     alert('truw');
  //         $(".search-ct").css("opacity", "1");

  // }else{
  //     alert('false');
  //         $(".search-ct").css("opacity", "0");

  // }


</script>