<style>
    h1{
        font-family:"arial  "
    }
</style>
<table>
    <thead>
        <tr>
        <th collspan="10"><strong ><h1 style="font-size:24px;">SUN PHARMA LABORATORIES LTD.</h1></strong></th>
</tr>   
<tr>
        <th collspan="5">SUN HOUSE, PLOT NO.201 B/1 , WESTERN EXPRESS HIGHWAY</th>
</tr>
<tr>
        <th collspan="5">GOREGAON (E) – MUMBAI – 400063</th>
</tr>
        <tr>
        <th collspan="5">Phone: 022-43244324    Fax: 022-43244343</th>
        </tr>
        <tr style="border:1px;">
            <th><strong>QUOTATION TO BHAGWAN MAHAVEER MEDICAL STORE, MALA MEDICAL STORE TEERTHANKER MAHAVEER UNIVERSITY TMU, TEERTHANKER MAHAVEER UNIVERSITY (TMU), DELHI ROAD, NH 24, BAGADPUR,UTTAR PRADESH 244001</strong></th>
        </tr>
    <tr>
        <!-- <th collspan="5">SUN PHARMA LABORATORIES LTD.</th> -->
        <th>No.</th>
        <th>institution name</th>
        <th>institution code</th>
        <th>key account name</th>
        <th>city</th>
        <th>region</th>
        <th>hq</th>
        <th>zone</th>
        <th>retailer name 1</th>
        <th>retailer name 2</th>
        <th>retailer name 3</th>
        <th>address</th>
    </tr>
    </thead>
    <tbody>
    @foreach($invoices as $item)
        <tr>
        <td>{{$item->id}}</td>
            <td>{{$item->institution_name}}</td>
            <td>{{$item->institution_code }}</td>
            <td>{{$item->key_account_name}}</td>

            <td>{{$item->city}}</td>
            <td>{{$item->region}}</td>
            <td>{{$item->hq }}</td>
            <td>{{$item->zone}}</td>

            <td>{{$item->retailer_name_1}}</td>
            <td>{{$item->retailer_name_2}}</td>
            <td>{{$item->retailer_name_3 }}</td>
            <td>{{$item->address}}</td>
        </tr>
    @endforeach
    </tbody>
</table>