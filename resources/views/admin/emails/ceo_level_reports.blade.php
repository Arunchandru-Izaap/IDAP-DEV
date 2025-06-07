<h5 style="font-weight: normal">
Dear Team,
<br/><br/>
@if($report_type == 'daily')
Please find the attached daily institutions report send for CEO Approval as on {{ $yesterday_date_formated }}
<br/><br/>
@elseif($report_type == 'monthly')
Please find the attached monthly institutions report send for CEO Approval as on {{ $lastMonthYear }}
<br/><br/>
@endif
<div></div>
For support , please mail to <a href="mailto:IDAP.INSTRA@sunpharma.com" >IDAP.INSTRA@sunpharma.com</a>
<br/><br/>
iDAP Help Desk,<br/>
Sun Pharma
</h5>