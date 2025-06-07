@extends('layouts.admin.app')
@section('content')
<style type="text/css">
  .no_activity_list
  {
    margin-top: 1rem;
  }
  .filter_section
  {
    margin-top: 1rem;
  }
  #loader1
    {
      background-image: url(../../images/loader.gif);
        background-repeat: no-repeat;
        background-position: center;
        background-size: 70px;
        height: 100vh;
        width: 100vw;
        display: block;
        position: fixed;
        top: 0;
        left: 0;
        overflow: hidden;
        background-color: rgba(241, 242, 243, 0.9);
        z-index: 9999;
    }
    .activity-tracker {
      /*max-height: 400px; 
      overflow-y: auto;*/
      overflow-x: hidden; 
      word-wrap: break-word; 
      white-space: normal;  
    }

    ul#myList {
      list-style-type: none;
      padding-left: 0;
    }
    .select2-container--classic .select2-selection--multiple .select2-selection__choice, .select2-container--default .select2-selection--multiple .select2-selection__choice, .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    /*background-color: #2255a4;
    border-color: #2255a4;*/
    color: #000;
}
.activity-tracker ul li {
    display: none;
    margin: 25px 0;
}
.activity-tracker ul li div:before {
    background: url(../../admin/images/acclock.svg);
    width: 32px;
    height: 32px;
    display: block;
    content: "";
    border-radius: 50%;
}
.activity-tracker ul li div h5 {
    font-size: 16px;
    font-weight: normal;
    font-stretch: normal;
    font-style: normal;
    line-height: normal;
    letter-spacing: -0.6px;
    text-align: left;
    color: #383838;
    margin: 0;
    padding: 0 0 0 20px;
}
.select2-container .select2-selection--multiple {
      max-height: 60px;
      overflow-y: auto;
      overflow-x: hidden;
        }
</style>
<!-- <div class="container"><h2>Initiator Dashboard demo</h2></div> -->
<!-- Page content wrapper-->

  <!-- Page content-->
  <div class="container-fluid">
    <div class="row">
      <div id='loader1' style='display: none;'>
                              
      </div>
      <div class="col-md-12 send-quotation">
      </div>
      <div class="col">
        <div class="actions-dashboard">
          <!-- <h5>Select Action</h5> -->
          <div class="row">
            <div class="col-md-6 filter_section  float-right1">
              <label>Filter Instituion: </label>
              <select name="activity_type" class=" js-example-basic-single" id="institution_filter" multiple="" data-placeholder="Select Institution">
                <option value=''> Select </option>
                @foreach($institutions as $institution)
                    <option value="{{ $institution['institution_id'] }}">{{ $institution['hospital_name'] }}-{{ $institution['institution_id'] }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4 filter_section  float-right">
              <label>Filter Activity: </label>
              <select name="activity_type" class=" js-example-basic-single" id="activity_filter" multiple="" data-placeholder="Select Activity To Filter">
                <option value='all'> All </option>
                @foreach($formattedTypes as $type)
                    <option value="{{ $type['db_value'] }}">{{ $type['display_value'] }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2  float-right mt-5">
              <a class="btn btn-primary filter_option" href="javascript:void(0)" id="">View</a>
            </div>
          </div>
          <div class="activity-tracker">
            <ul id="myList">
	        
                
            </ul>
            <button id="loadMore" class="btn btn-warning" style="display: none">VIEW MORE</button>
        </div>
        <div class="no_activity_list d-none">No Activity found <!-- for <span id="selectedFilter"></span> --></div>
        </div>
      </div>
    </div>
  </div>
      <style type="text/css">
        p.copy-center.copyright{
          position: inherit;
          padding: 10px 0;
        }
        #page-content-wrapper nav{
	        background: #fff;
        }
      </style>
@endsection
@push('scripts')
      <script src="{{asset('frontend/js/select2.js')}}"></script>
      <script type="text/javascript">
        $('#activity_filter').val('all');
        $('.js-example-basic-single').select2();
        $('#activity_filter').on('select2:select', function(e) {
          var selected = $(this).val();
          if (selected.includes('all')) {
            $(this).val('all').trigger('change'); // Select only 'All' option
          }
        });
        $('#activity_filter').on('select2:unselect', function(e) {
          var selected = $(this).val();
          if (!selected) {
            $(this).val(null).trigger('change'); // Clear selection if all options are unselected
          }
        });
        $('.filter_option').on('click', function(){
          if($('#institution_filter').val() == '')
          {
            alert('Please select Institution');
            return;
          }
          if($('#activity_filter').val() == '')
          {
            alert('Please select Activity');
            return;
          }
          setTimeout(function() {
            console.log("1 second passed, continuing...");
          }, 1000);
          var settings = {
            "url": "/admin/activity_filter_new",
            "method": "POST",
            "timeout": 0,
            "headers": {
              "Accept-Language": "application/json",
              "Content-Type": "application/json"
            },
            "data": JSON.stringify({
              "_token": "{{ csrf_token() }}",
              "activity_value": $('#activity_filter').val(),
              "institution_id": $('#institution_filter').val(),
            })
          };
          $('#loader1').show(); 
          $('.no_activity_list').addClass('d-none')
          $.ajax(settings).done(function (response) {
            $('.activity-tracker').removeClass('d-none');
            $('#loader1').hide(); 
            if(response.status == true)
            {
              $('#myList').empty();
              $.each(response.data, function(index, activity) {
                var activityDate = new Date(activity.created_at);
                var formattedTime = activityDate.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
                var formattedDate = activityDate.toLocaleDateString('en-IN', { day: '2-digit', month: '2-digit', year: 'numeric' });

                // Check if JSON data exists and create the download link
                var downloadLink = '';
                if (activity.json_data) {
                    downloadLink = `
                    <a href="${window.location.href}?download=true&activity_id=${activity.activity_id}" class="btn btn-link" data-title="Download">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                        </svg>
                    </a>`;
                }

                // Meta data, if available
                var metaData = activity.meta_data != null ? `<p>${activity.meta_data}</p>` : '';

                // Create the list item with formatted activity and metadata
                var li = `
                <li>
                    <div>
                        <h5>${downloadLink} At ${formattedTime}, ${formattedDate} - for ${activity.hospital_name}-${activity.institution_id} - ${activity.activity} </h5>
                        ${metaData}
                    </div>
                </li>`;
                $('#myList').append(li);
              });
              size_li = $("#myList li").length;
              x = 3;
              $('#myList li:lt(' + x + ')').show();
              $('#loadMore').hide();
              if(size_li > 3)
              {
                $('#loadMore').show();
              }
            }
            else
            {
              $('.no_activity_list').removeClass('d-none')
              $('.activity-tracker').addClass('d-none');
              $('#selectedFilter').text($('#activity_filter option:selected').text())
            }
          });
        })
        $('#loadMore').click(function () {
            x= (x+5 <= size_li) ? x+5 : size_li;
            $('#myList li:lt('+x+')').show();
             $('#showLess').show();
            if(x == size_li){
                $('#loadMore').hide();
            }
        });
      </script>
@endpush