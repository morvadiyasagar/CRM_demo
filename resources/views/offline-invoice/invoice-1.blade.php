<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    
    <title>@lang('app.invoice')</title>
    <style>
        /* Please don't remove this code it is useful in case of add new language in dompdf */

        /* @font-face {
            font-family: Hind;
            font-style: normal;
            font-weight: normal;
            src: url({{ asset('fonts/hind-regular.ttf') }}) format('truetype');
        } */

         /* For hindi language  */

        /* * {
           font-family: Hind, DejaVu Sans, sans-serif;
        } */

         /* For japanese language */

        @font-face {
            font-family: 'THSarabun';
            font-style: normal;
            font-weight: normal;
            src: url("{{ asset('fonts/TH_Sarabun.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'THSarabun';
            font-style: normal;
            font-weight: bold;
            src: url("{{ asset('fonts/TH_SarabunBold.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'THSarabun';
            font-style: italic;
            font-weight: bold;
            src: url("{{ asset('fonts/TH_SarabunBoldItalic.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'THSarabun';
            font-style: italic;
            font-weight: bold;
            src: url("{{ asset('fonts/TH_SarabunItalic.ttf') }}") format('truetype');
        }

        @php
            $font = '';
            if($company->locale == 'ja') {
                $font = 'ipag';
            } else if($company->locale == 'hi') {
                $font = 'hindi';
            } else if($company->locale == 'th') {
                $font = 'THSarabun';
            } else {
                $font = 'noto-sans';
            }
        @endphp

        * {
            font-family: {{$font}}, DejaVu Sans , sans-serif;
            
        }
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        a {
            color: #0087C3;
            text-decoration: none;
        }

        body {
            position: relative;
            width: 100%;
            height: auto;
            margin: 0 auto;
            color: #555555;
            background: #FFFFFF;
            font-size: 14px;
            font-family: Verdana, Arial, Helvetica, sans-serif;
        }

        h2 {
            font-weight:normal;
        }

        header {
            padding: 10px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #AAAAAA;
        }

        #logo {
            float: left;
            margin-top: 11px;
        }

        #logo img {
            height: 55px;
            margin-bottom: 15px;
        }

        #company {

        }

        #details {
            margin-bottom: 50px;
        }

        #client {
            padding-left: 6px;
            float: left;
        }

        #client .to {
            color: #777777;
        }

        h2.name, div.name {
            font-size: 1.2em;
            font-weight: normal;
            margin: 0;
        }

        #invoice {

        }

        #invoice h1 {
            color: #0087C3;
            font-size: 2.4em;
            line-height: 1em;
            font-weight: normal;
            margin: 0 0 10px 0;
        }

        #invoice .date {
            font-size: 1.1em;
            color: #777777;
        }

        table {
            width: 100%;
          
            margin-bottom: 20px;
        }

        table th,
        table td {
            padding: 5px 10px 7px 10px;
            background: #EEEEEE;
            
            border-bottom: 1px solid #FFFFFF;
        }

        table th {
            white-space: nowrap;
            font-weight: normal;
        }

        table td {
            text-align: right;
        }

        table td.desc h3, table td.qty h3 {
            color: #57B223;
           
            font-weight: normal;
            margin: 0 0 0 0;
        }

        table .no {
            color: #FFFFFF;
           
            background: #1E90FF;
           
        }

        table .desc {
            text-align: left;
        }

        table .unit {
            background: #DDDDDD;
        }


        table .total {
            background: #57B223;
            color: #FFFFFF;
        }

       

        table td.unit{
            width: 40%;
        }

        table td.desc{
            width: 20%;
        }

        table td.qty{
            width: 8%;
        }

        .status {
            margin-top: 15px;
            padding: 1px 8px 5px;
            font-size: 1.3em;
            width: 80px;
            color: #fff;
            float: right;
            text-align: center;
            display: inline-block;
        }

        .status.unpaid {
            background-color: #E7505A;
        }
        .status.paid {
            background-color: #26C281;
        }
        .status.cancelled {
            background-color: #95A5A6;
        }
        .status.error {
            background-color: #F4D03F;
        }

        table tr.tax .desc {
            text-align: right;
            color: #1BA39C;
        }
        table tr.discount .desc {
            text-align: right;
            color: #E43A45;
        }
        table tr.subtotal .desc {
            text-align: right;
            color: #1d0707;
        }
        table tbody tr:last-child td {
            border: none;
        }

        table tfoot td {
        
            background: #FFFFFF;
            border-bottom: none;
            
            white-space: nowrap;
           
        }

        table tfoot tr:first-child td {
            border-top: none;
        }

        table tfoot tr td:first-child {
            border: none;
        }

        #thanks {
            font-size: 2em;
            margin-bottom: 50px;
        }

        #notices {
            padding-left: 6px;
            border-left: 6px solid #0087C3;
        }

        #notices .notice {
            font-size: 1.2em;
        }

        footer {
            color: #777777;
            width: 100%;
            height: 30px;
            position: absolute;
            bottom: 0;
            border-top: 1px solid #AAAAAA;
           
            text-align: center;
        }

        table.billing td {
            background-color: #fff;
        }

        table td div#invoiced_to {
            text-align: right;
        }

        #notes{
            color: #767676;
            font-size: 11px;
        }

        .item-summary{
            font-size: 12px
        }

        .mb-3{
            margin-bottom: 1rem;
        }
        .logo {
            text-align: right;
        }
        .logo img {
            max-width: 150px;
        }
    </style>
</head>
<body>
<header class="clearfix">

    <table cellpadding="0" cellspacing="0" class="billing">
    <tr>
        <td colspan="2"  style="text-align:right;">
        <h3 style=" font-weight: bold;" class="name">@lang("modules.invoices.bill")</h3>
        </td>
    </tr>
        <tr >
        <td style="text-align:left;" >
        <div >
                    <small style=" font-weight: bold;">@lang("modules.invoices.generatedBy"):</small>
                    {{ ucwords($generatedBy->company_name) }}
{{--                    @if(!is_null($settings))--}}
                            <div>{!! nl2br($generatedBy->address) !!}</div>
                        <div>{{ $generatedBy->company_phone }}</div>
{{--                    @endif--}}
                </div>
               
                  
                    
            </td>
            <td style="text-align:right;padding-left:40%;"  >

                   
            <table    >
                    <tr >
                    <td style="background-color:1E90FF;color: #FFFFFF;" >@lang("modules.invoices.number")</td>
                    <td style="background-color:#EEEEEE;">#{{ ($invoice->id < 10) ? "0".$invoice->id : $invoice->id }}</td>
                    </tr>
                    <tr >
                    <td style="background-color:1E90FF;color: #FFFFFF;">@lang("modules.invoices.date")</td>
                    <td style="background-color:#EEEEEE;;">{{ $invoice->pay_date->format($global->date_format ? $global->date_format : 'Y-m-d') }}</td>
                    </tr>
                    
                    </table>

            </td>
            
        </tr>
        <tr >
        <td></td>
        <td style="text-align: left;background-color: #EEEEEE;" >
        <div>
                    <small>@lang("modules.invoices.billedTo"):</small>
                    {{ ucwords($company->company_name) }}
                    <div>{!! nl2br($company->address) !!}</div>
                    <div>@lang('app.cif'): {{ $company->id_number }}</div>

                </div>
       
        
        </td>
        </tr>
    </table>
</header>
<main>
   
    <table border="0" cellspacing="0" cellpadding="0">
        <thead>
        <tr>
          
            <th style="width: 50%;" class="no desc" style="text-align: left;">@lang("modules.invoices.concept")</th>
            <th style="width: 10%;" class="no desc">@lang("modules.invoices.quantity")</th>
            <th  style="width: 20%;text-align:right;"  class="no ">@lang("modules.invoices.price")</th>
            <th style="width: 20%;text-align:right;"  class="no ">@lang("modules.invoices.total") </th>
        </tr>
        </thead>
        <tbody>
            <tr>
            <td >{{ ucfirst($invoice->package->name)  }} - {{ ucfirst($invoice->package_type)  }}</td>
            <td >{{ $invoice->pay_date->format($global->date_format) }} - {{ $invoice->next_pay_date->format($global->date_format) }}</td>
            <td ></td>
            <td >{{ number_format((float)$invoice->amount, 2, '.', '') }}</td>
            </tr>
       
        </tbody>
        <tfoot>

        <tr  >
            <td >&nbsp;</td>
            <td >&nbsp;</td>
            <td >@lang("modules.invoices.subTotal")</td>
            <td >{{ number_format((float)$invoice->amount, 2, ',', '') }}</td>
        </tr>
      
       @if( $invoice->irpf != '')
        {{ $irpf= ($invoice->irpf * $invoice->amount)/100 }}
        {{ $totalamt= ($invoice->amount- $irpf) }}
        <tr  >
            <td >&nbsp;</td>

            <td colspan="2" >@lang("modules.invoices.irpf") {{ $invoice->irpf }} % (Base:{{ number_format((float)$invoice->amount, 2, ',', '') }})</td>
            <td >-{{ number_format((float)$irpf, 2, ',', '') }}</td>

        </tr>
        @endif
      
        
            <tr >
            
                <td >&nbsp;</td>
                <td colspan="2">@lang("modules.invoices.total")(EUR)</td>
                <td >{{ number_format((float)$totalamt, 2, ',', '') }} {!! htmlentities($generatedBy->currency->currency_symbol)  !!}</td>
            </tr>
           
          
            <tr>
          
          <td colspan="2" class="no" style="text-align:left ;">@lang("modules.invoices.expiration")</th>
          <td colspan="2" class="no"  style="text-align:left ;">@lang("modules.invoices.paymentmethod")</th>
        
      </tr>
      <tr>
          
          <td style="text-align:left ;" colspan="2">{{ $invoice->pay_date->format($global->date_format ? $global->date_format : 'Y-m-d') }}</th>
          <td colspan="2"></th>
        
      </tr>
        </tfoot>
    </table>
    <p>&nbsp;</p>
    <hr>
   
    <p id="notes">
        @lang("app.note"): Here {!! htmlentities($generatedBy->currency->currency_symbol)  !!} refers to {!! $generatedBy->currency->currency_code  !!}<br>
    </p>

</main>
</body>
</html>