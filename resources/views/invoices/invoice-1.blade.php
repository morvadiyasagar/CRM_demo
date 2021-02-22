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
        <h3 class="name">@lang("modules.invoices.bill")</h3>
        </td>
    </tr>
        <tr >
        <td style="text-align:left;" >
        <div  >
            
            @if(!is_null($invoice->project) && !is_null($invoice->project->client))
                <small>@lang("modules.invoices.client"):</small>
                <div>
                    <span>{{ ucwords($invoice->project->client->name) }}</span>
                </div>
                <div>@lang('app.nif'): {{ $invoice->project->client->id_number }}</div>

                <div>{{ ucwords($invoice->project->client->company_name) }}</div>
                <div>
                   
                    <div>  <b>@lang('app.address') :</b> {!! nl2br($invoice->project->clientdetails->address) !!}</div>
                </div>
                @if ($invoice->show_shipping_address === 'yes')
                    <div>
                        <b>@lang('app.shippingAddress') :</b>
                        <div>{!! nl2br($invoice->project->clientdetails->shipping_address) !!}</div>
                    </div>
                @endif
                <div>
                    <span>{{ $invoice->project->client->email }}</span>
                </div>

                @if($invoiceSetting->show_gst == 'yes' && !is_null($invoice->project->client->gst_number))
                    <div> @lang('app.gstIn'): {{ $invoice->project->client->gst_number }} </div>
                @endif
            @elseif(!is_null($invoice->client_id) && !is_null($invoice->clientdetails))
                <small>@lang("modules.invoices.billedTo"):</small>
                <div class="name">
                    <span class="bold">{{ ucwords($invoice->clientdetails->name) }}</span>
                </div>
                <div>{{ ucwords($invoice->clientdetails->company_name) }}</div>
                <div class="mb-3">
                    <b>@lang('app.address') :</b>
                    <div>{!! nl2br($invoice->clientdetails->address) !!}</div>
                </div>
                @if ($invoice->show_shipping_address === 'yes')
                    <div>
                        <b>@lang('app.shippingAddress') :</b>
                        <div>{!! nl2br($invoice->clientdetails->shipping_address) !!}</div>
                    </div>
                @endif
                <div>
                    <span>{{ $invoice->clientdetails->email }}</span>
                </div>

                @if($invoiceSetting->show_gst == 'yes' && !is_null($invoice->clientdetails->gst_number))
                    <div> @lang('app.gstIn'): {{ $invoice->clientdetails->gst_number }} </div>
                @endif
            @endif

        </div>
               
                  
                    
            </td>
            <td style="text-align:right;padding-left:10%;"  >

                   
            <table    >
                    <tr >
                    <td style="background-color:1E90FF;color: #FFFFFF;" >@lang("modules.invoices.number")</td>
                    <td style="background-color:#EEEEEE;">{{ $invoice->invoice_number}}</td>
                    </tr>
                    <tr >
                    <td style="background-color:1E90FF;color: #FFFFFF;">@lang("modules.invoices.date")</td>
                    <td style="background-color:#EEEEEE;;">{{ $invoice->issue_date->format($company->date_format) }}</td>
                    </tr>
                    
                    </table>

            </td>
            
        </tr>
        <tr >
        <td></td>
        <td style="text-align: left;background-color: #EEEEEE;" >
        
        <div >
                     
        <small>@lang("modules.invoices.generatedBy"):</small>     

                    </div>
                    <div>{{ ucwords($company->company_name) }}</div>
                    <div>@lang('app.cif'): {{ $company->id_number }}</div>
                    @if(!is_null($settings))
                        <div>{!! nl2br($company->address) !!}</div>
                        <div>{{ $company->company_phone }}</div>
                    @endif
                    @if($invoiceSetting->show_gst == 'yes' && !is_null($invoiceSetting->gst_number))
                        <div>@lang('app.gstIn'): {{ $invoiceSetting->gst_number }}</div>
                    @endif

        
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
        <?php $count = 0; ?>
        @foreach($invoice->items as $item)
            @if($item->type == 'item')
            <tr style="page-break-inside: avoid;">
               
                <td class="desc">{{ ucfirst($item->item_name) }}
                   
                </td>
                <td class="qty">{{ $item->quantity }}</td>
                <td class="qty">{{ number_format((float)$item->unit_price, 2, '.', '') }}</td>
                <td class="qty">{{ number_format((float)$item->amount, 2, '.', '') }}</td>
            </tr>
            @endif
        @endforeach
       
       
        </tbody>
        <tfoot>
        <tr  >
            <td >&nbsp;</td>
            <td >&nbsp;</td>
            <td >@lang("modules.invoices.subTotal")</td>
            <td >{{ number_format((float)$invoice->sub_total, 2, ',', '') }}</td>
        </tr>
        @if($discount != 0 && $discount != '')
        <tr >
            <td >&nbsp;</td>
            <td >&nbsp;</td>
            <td >@lang("modules.invoices.discount")</td>
            <td>-{{ number_format((float)$discount, 2, ',', '') }}</td>
        </tr>
        @endif
        {{ $i=0}}
        @foreach($taxes as $key=>$tax)
       
            <tr >
                <td >&nbsp;</td>
                <td >&nbsp;</td>
                <td >{{ strtoupper("IVA ").$taxeper[$i] }} % (Base:{{ number_format((float)$tax *100 / $taxeper[$i], 2, ',', '')}}  ) </td>
                <td >{{ number_format((float)$tax, 2, ',', '') }}</td>
            </tr>
            {{ $i=$i+1}}
        @endforeach
        @if($invoice->irpf != 0 && $invoice->irpf != '')
        {{ $irpf= ($invoice->irpf * $invoice->sub_total)/100 }}
        <tr  >
            <td >&nbsp;</td>

            <td colspan="2" >@lang("modules.invoices.irpf") {{ $invoice->irpf }} % (Base:{{ number_format((float)$invoice->sub_total, 2, ',', '') }})</td>
            <td >-{{ number_format((float)$irpf, 2, ',', '') }}</td>
        </tr>
        @endif



            <tr >
            
                <td >&nbsp;</td>
                <td  colspan="2"   ><b> @lang("modules.invoices.total")({!! htmlentities($invoice->currency->currency_code)  !!}) </b></td>
                <td style="font-family: 'examplefont', sans-serif;">  {{ number_format((float)$invoice->total, 2, ',', '') }} €</td>
            </tr>
            @if ($invoice->credit_notes()->count() > 0)
                <tr>
                    <td  colspan="3">@lang('modules.invoices.appliedCredits')</td>
                    <td style="font-family: 'examplefont', sans-serif;">{{ number_format((float)$invoice->appliedCredits(), 2, ',', '') }} €</td>
                </tr>
            @endif
            <tr >
            
                <td  style="padding-bottom: -10px;"   colspan="3"> @lang("modules.invoices.paid")</td>
                <td style="font-family: 'examplefont', sans-serif;"> {{ number_format((float)$invoice->amountPaid(), 2, ',', '') }} €</td>
            </tr>
            <tr >
                <td   colspan="3"> @lang("modules.invoices.pending")</td>
                <td style="font-family: 'examplefont', sans-serif;">{{ number_format((float)$invoice->amountDue(), 2, ',', '') }}  €</td>
            </tr>
            <tr>
          
          <td colspan="2" class="no" style="text-align:left ;">@lang("modules.invoices.expiration")</th>
          <td colspan="2" class="no"  style="text-align:left ;">@lang("modules.invoices.paymentmethod")</th>
        
      </tr>
      <tr>
          
          <td style="text-align:left ;" colspan="2">{{ $invoice->issue_date->format($company->date_format) }}</th>
          <td colspan="2"></th>
        
      </tr>
        </tfoot>
    </table>
    <p>&nbsp;</p>
    <hr>
    <p id="notes">
        @if(!is_null($invoice->note))
            {!! nl2br($invoice->note) !!}<br>
        @endif
        @if($invoice->status == 'unpaid')
        @lang("modules.invoices.noteinfo")
       
        @endif

    </p>

</main>
</body>
</html>