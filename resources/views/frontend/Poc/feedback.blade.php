@extends('layouts.frontend.app')
@section('content')
<div id="page-content-wrapper">
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                <ul class="navbar-nav dashboard-nav">
                    <li class="nav-item active"><a class="nav-link" href="{{route('poc_dashboard')}}"> <img src="../admin/images/back.svg">Feedback Form</a></li>
                </ul>

                <ul class="d-flex ml-auto user-name">
                    <li>
                        <h3>{{Session::get('emp_name')}}</h3>
                        <p>Poc</p>
                    </li>

                    <li>
                        <img src="{{ asset('admin/images/Sun_Pharma_logo.png') }}">
                    </li>
                </ul>
            </div>
            
        </div>

    </nav>

    <ul class="bradecram-menu">
        <li><a href="{{route('poc_dashboard')}}">
            Home
        </a></li>
        <li class="active">
            <a href="">
                POC Feedback
            </a>
        </li>
    </ul>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col">
            @if (session('status'))
                <div class="alert alert-success mt-3" role="alert">
                    {{ session('message') }}
                </div>
            @endif
                <form class="actions-dashboard p-3" method="POST" action="{{ route('poc_feedback') }}">
                    @csrf()
                    <h4>POC Feedback Form</h4>
                    <h5>Select Institution</h5>
                    <div class="start-end-date">
                        <div class="input-group">
                            <select name="institutes[]" id="institute_drop" multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="100" onchange="console.log(this.selectedOptions)" required="">
                                @foreach($data as $institution)
                                <option value="{{$institution->id}}"> {{$institution->hospital_name}} - {{$institution->institution_id}} - {{$institution->city}} - {{$institution->rev_no}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <h5>Add Comment</h5>
                    <div class="start-end-date">
                        <div class="input-group">
                                <textarea class="form-control" name="comment" placeholder="Comment" required=""></textarea>
                        </div>
                    </div>

                    <div class="input-group m-3">
                        <button type="submit" class="orange-btn" id="add_new_counter_btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- <script>
    var style = document.createElement('style');
    style.setAttribute("id","multiselect_dropdown_styles");
    style.innerHTML = `
    .select2 {
    width: 100% !important;
    }
    .multiselect-dropdown{
    display: inline-block;
    padding: 2px 5px 0px 5px;
    border-radius: 4px;
    border: solid 1px #ced4da;
    background-color: white;
    position: relative;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right .75rem center;
    background-size: 16px 12px;
    }
    .multiselect-dropdown span.optext, .multiselect-dropdown span.placeholder{
    margin-right:0.5em; 
    margin-bottom:2px;
    padding:1px 0; 
    border-radius: 4px; 
    display:inline-block;
    }
    .multiselect-dropdown span.optext{
    background-color:lightgray;
    padding:1px 0.75em; 
    }
    .multiselect-dropdown span.optext .optdel {
    float: right;
    margin: 0 -6px 1px 5px;
    font-size: 0.7em;
    margin-top: 2px;
    cursor: pointer;
    color: #666;
    }
    .multiselect-dropdown span.optext .optdel:hover { color: #c66;}
    .multiselect-dropdown span.placeholder{
    color:#ced4da;
    }
    .multiselect-dropdown-list-wrapper{
    box-shadow: gray 0 3px 8px;
    z-index: 100;
    padding:2px;
    border-radius: 4px;
    border: solid 1px #ced4da;
    display: none;
    margin: -1px;
    position: absolute;
    top:0;
    left: 0;
    right: 0;
    background: white;
    }
    .multiselect-dropdown-list-wrapper .multiselect-dropdown-search{
    margin-bottom:5px;
    }
    .multiselect-dropdown-list{
    padding:2px;
    height: 15rem;
    overflow-y:auto;
    overflow-x: hidden;
    }
    .multiselect-dropdown-list::-webkit-scrollbar {
    width: 6px;
    }
    .multiselect-dropdown-list::-webkit-scrollbar-thumb {
    background-color: #bec4ca;
    border-radius:3px;
    }

    .multiselect-dropdown-list div{
    padding: 5px;
    }
    .multiselect-dropdown-list input{
    height: 1.15em;
    width: 1.15em;
    margin-right: 0.35em;  
    }
    .multiselect-dropdown-list div.checked{
    }
    .multiselect-dropdown-list div:hover{
    background-color: #ced4da;
    }
    .multiselect-dropdown span.maxselected {width:100%;}
    .multiselect-dropdown-all-selector {border-bottom:solid 1px #999;}
    `;
    document.head.appendChild(style);

    function MultiselectDropdown(options){
    var config={
        search:true,
        height:'15rem',
        placeholder:'Select Institution',
        txtSelected:'selected',
        txtAll:'All',
        txtRemove: 'Remove',
        txtSearch:'search',
        ...options
    };
    function newEl(tag,attrs){
        var e=document.createElement(tag);
        if(attrs!==undefined) Object.keys(attrs).forEach(k=>{
        if(k==='class') { Array.isArray(attrs[k]) ? attrs[k].forEach(o=>o!==''?e.classList.add(o):0) : (attrs[k]!==''?e.classList.add(attrs[k]):0)}
        else if(k==='style'){  
            Object.keys(attrs[k]).forEach(ks=>{
            e.style[ks]=attrs[k][ks];
            });
        }
        else if(k==='text'){attrs[k]===''?e.innerHTML='&nbsp;':e.innerText=attrs[k]}
        else e[k]=attrs[k];
        });
        return e;
    }

    
    document.querySelectorAll("select[multiple]").forEach((el,k)=>{
        
        var div=newEl('div',{class:'multiselect-dropdown',style:{width:"100%"}});
        el.style.display='none';
        el.parentNode.insertBefore(div,el.nextSibling);
        var listWrap=newEl('div',{class:'multiselect-dropdown-list-wrapper'});
        var list=newEl('div',{class:'multiselect-dropdown-list',style:{height:config.height}});
        var search=newEl('input',{class:['multiselect-dropdown-search'].concat([config.searchInput?.class??'form-control']),style:{width:'100%',display:el.attributes['multiselect-search']?.value==='true'?'block':'none'},placeholder:config.txtSearch});
        listWrap.appendChild(search);
        div.appendChild(listWrap);
        listWrap.appendChild(list);

        el.loadOptions=()=>{
        list.innerHTML='';
        
        if(el.attributes['multiselect-select-all']?.value=='true'){
            var op=newEl('div',{class:'multiselect-dropdown-all-selector'})
            var ic=newEl('input',{type:'checkbox'});
            op.appendChild(ic);
            op.appendChild(newEl('label',{text:config.txtAll}));
    
            op.addEventListener('click',()=>{
            op.classList.toggle('checked');
            op.querySelector("input").checked=!op.querySelector("input").checked;
            
            var ch=op.querySelector("input").checked;
            list.querySelectorAll(":scope > div:not(.multiselect-dropdown-all-selector)")
                .forEach(i=>{if(i.style.display!=='none'){i.querySelector("input").checked=ch; i.optEl.selected=ch}});
    
            el.dispatchEvent(new Event('change'));
            });
            ic.addEventListener('click',(ev)=>{
            ic.checked=!ic.checked;
            });
            el.addEventListener('change', (ev)=>{
            let itms=Array.from(list.querySelectorAll(":scope > div:not(.multiselect-dropdown-all-selector)")).filter(e=>e.style.display!=='none')
            let existsNotSelected=itms.find(i=>!i.querySelector("input").checked);
            if(ic.checked && existsNotSelected) ic.checked=false;
            else if(ic.checked==false && existsNotSelected===undefined) ic.checked=true;
            });
    
            list.appendChild(op);
        }

        Array.from(el.options).map(o=>{
            var op=newEl('div',{class:o.selected?'checked':'',optEl:o})
            var ic=newEl('input',{type:'checkbox',checked:o.selected});
            op.appendChild(ic);
            op.appendChild(newEl('label',{text:o.text}));

            op.addEventListener('click',()=>{
            op.classList.toggle('checked');
            op.querySelector("input").checked=!op.querySelector("input").checked;
            op.optEl.selected=!!!op.optEl.selected;
            el.dispatchEvent(new Event('change'));
            });
            ic.addEventListener('click',(ev)=>{
            ic.checked=!ic.checked;
            });
            o.listitemEl=op;
            list.appendChild(op);
        });
        div.listEl=listWrap;

        div.refresh=()=>{
            div.querySelectorAll('span.optext, span.placeholder').forEach(t=>div.removeChild(t));
            var sels=Array.from(el.selectedOptions);
            if(sels.length>(el.attributes['multiselect-max-items']?.value??5)){
            div.appendChild(newEl('span',{class:['optext','maxselected'],text:sels.length+' '+config.txtSelected}));          
            }
            else{
            sels.map(x=>{
                var c=newEl('span',{class:'optext',text:x.text, srcOption: x});
                if((el.attributes['multiselect-hide-x']?.value !== 'true'))
                c.appendChild(newEl('span',{class:'optdel',text:'🗙',title:config.txtRemove, onclick:(ev)=>{c.srcOption.listitemEl.dispatchEvent(new Event('click'));div.refresh();ev.stopPropagation();}}));

                div.appendChild(c);
            });
            }
            if(0==el.selectedOptions.length) div.appendChild(newEl('span',{class:'placeholder',text:el.attributes['placeholder']?.value??config.placeholder}));
        };
        div.refresh();
        }
        el.loadOptions();
        
        search.addEventListener('input',()=>{
            /*list.querySelectorAll(":scope div:not(.multiselect-dropdown-all-selector)").forEach(d=>{
                var txt=d.querySelector("label").innerText.toUpperCase();
                d.style.display=txt.includes(search.value.toUpperCase())?'block':'none';
            });*/
            const query = search.value;
            if (query.length < 2) { // Start search after 2 characters
                return;
            }

            // Make an AJAX request to fetch matching institutions
            fetch(`/poc/institutions/search?q=${query}`)
                .then(response => response.json())
                .then(data => {
                    // Clear previous options
                    list.innerHTML = '';

                    // Add fetched options to the list
                    data.forEach(item => {
                        const op = newEl('div', { class: 'multiselect-dropdown-item', optEl: item });
                        const ic = newEl('input', { type: 'checkbox' });
                        op.appendChild(ic);
                        op.appendChild(newEl('label', { text: `${item.hospital_name} - ${item.institution_id} - ${item.city} - ${item.rev_no}` }));

                        // Set up click event to handle selection
                        op.addEventListener('click', () => {
                            op.classList.toggle('checked');
                            ic.checked = !ic.checked;
                            // Manually select in original select element if needed
                            // el.value = item.id; // Example if single select

                            el.dispatchEvent(new Event('change'));
                        });

                        list.appendChild(op);
                    });
                })
                .catch(error => console.error('Error fetching data:', error));
        });

        div.addEventListener('click',()=>{
        div.listEl.style.display='block';
        search.focus();
        search.select();
        });
        
        document.addEventListener('click', function(event) {
        if (!div.contains(event.target)) {
            listWrap.style.display='none';
            div.refresh();
        }
        });    
    });
    }

    window.addEventListener('load',()=>{
    MultiselectDropdown(window.MultiselectDropdownOptions);
    });
</script> -->
<script src="{{asset('frontend/js/select2.js')}}"></script>
<script type="text/javascript">
    $('#institute_drop').append(`<option value='all'>All</option>`)
    $('#institute_drop').select2({
    placeholder: 'Select institutions',
    ajax: {
        url: '/poc/institutions/search',
        dataType: 'json',
        delay: 250,
        data: function (params) {
            return { q: params.term || '' }; // Pass an empty query initially to load all
        },
        processResults: function (data) {
            let results = data.map(item => ({
                id: item.id,
                text: `${item.hospital_name} - ${item.institution_id} - ${item.city} - ${item.rev_no}`
            }));
            var searchterm = $('.select2-search__field').val()
            if(data.length > 0 && searchterm == '')
            {
                // Add "All" option at the beginning of the list
                results.unshift({ id: 'all', text: 'All' });
            }
            
            return { results: results };
        },
        cache: true
    },
    minimumInputLength: 0, // Load options immediately without search
    closeOnSelect: false  // Keep dropdown open for multi-select convenience
});

// Select "All" option by default
$('#institute_drop').on('select2:open', function () {
    if (!$('#institute_drop').val() || $('#institute_drop').val().length === 0) {
        // If no option is selected, select "All" by default
        //$('#institute_drop').val('all').trigger('change');
    }
});

// Handling "All" option selection to select all institutions
/*$('#institute_drop').on('select2:select', function (e) {
    if (e.params.data.id === 'all') {
        // Select all available options if "All" is chosen
        let allOptions = $('#institute_drop').find('option');
        $('#institute_drop').val(allOptions.map(function () {
            return this.value;
        }).get()).trigger('change');
        $('#institute_drop').find('option').not('[value="all"]').hide();
    }
    else {
        // Show all options if "All" is not selected
        $('#institute_drop').find('option').show();
    }
});*/
$('#institute_drop').on('select2:select', function(e) {
  var selected = $(this).val();
  if (selected.includes('all')) {
    $(this).val('all').trigger('change'); // Select only 'All' option
  }
});
$('#institute_drop').on('select2:unselect', function(e) {
  var selected = $(this).val();
  if (!selected) {
    $(this).val(null).trigger('change'); // Clear selection if all options are unselected
  }
});
$('.select2-search__field').on('input', function(e)
{
    if($(this).val()=='')
    {
        $('#institute_drop option[value="all"]').show();
    }
    else
    {
        $('.select2-results').find('.select2-results__option').each(function() {
            console.log($(this).find('li'))
            if ($(this).text().toLowerCase() === 'all') {
                $(this).hide(); // Hide "All" when searching
            } else {
                $(this).show(); // Show other options
            }
        });
    }
})
</script>
@endsection