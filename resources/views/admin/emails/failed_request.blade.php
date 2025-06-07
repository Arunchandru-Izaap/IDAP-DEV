<h5 style="font-weight: normal">
Dear,<br/><br/>

iDAP  Failed Send quotation VQ request.<br/>
<br/><br/>
<div>
    <table style="border-collapse: collapse; width: 100%; text-align: left;" border="1">
        <thead>
            <tr>
                <th style="padding: 8px; text-align: left;">S.No</th>
                <th style="padding: 8px; text-align: left;">VQ ID</th>
                <th style="padding: 8px; text-align: left;">Request Date</th>
                <th style="padding: 8px; text-align: left;">Response</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $i => $result)
            <tr>
                <td style="padding: 8px;">{!! $i + 1 !!}</td>
                <td style="padding: 8px;">{{$result->vq_id}}</td>
                <td style="padding: 8px;">{{$result->created_at}}</td>
                <td style="padding: 8px;">{{ $result->response ? $result->response : 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<br/><br/>
<!-- For support , please mail to <a href="mailto:IDAP.INSTRA@sunpharma.com" >IDAP.INSTRA@sunpharma.com</a>
<br/><br/>
iDAP Help Desk,<br/>
Sun Pharma -->
</h5>