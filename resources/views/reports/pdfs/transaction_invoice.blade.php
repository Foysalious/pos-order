<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice</title>

    <style>
        * {
            padding: 0;
            margin: 0;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            outline: none;
            border: none;
        }

        body {
            position: relative;
            -webkit-transition: all 0.3s ease-in-out;
            transition: all 0.3s ease-in-out;
            counter-reset: my-sec-counter;
            scroll-behavior: smooth;
            font-family: "roboto";
        }

        .headers {
            width: 100%;
            margin: 0 auto;
            text-align: left;
            border-spacing: 0px;
            border-collapse: collapse;
            background-color: #D8F3DC;
            padding: 15px 0;
        }

        @media (min-width: 1281px) {
            .headers {
                width: 1280px;
            }
        }

        .headers tbody tr .header-left {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            padding: 15px 0;
        }

        .headers tbody tr .header-left img {
            padding: 0 10px;
        }

        .headers tbody tr .header-left div {
            margin: 0 0 0 10px;
        }

        .headers tbody tr .header-right {
            text-align: right;
            padding: 0 10px;
        }

        .header {
            background-color: #D8F3DC;
            padding: 15px 0;
        }

        .header .container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 10px;
        }

        .header .container .d-flex {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
        }

        .header .container .justify-content-between {
            -webkit-box-pack: justify;
            -ms-flex-pack: justify;
            justify-content: space-between;
        }

        .header .container .align-items-center {
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
        }

        .header .container .sr-company .logo-content {
            padding: 0 0 0 10px;
        }

        .header .container .sr-company .logo-content h4 {
            margin: 0px;
        }

        .header .container .sr-company .logo-content p {
            margin: 0px;
            font-size: 16px;
        }

        .table-invoice-date {
            width: 100%;
            margin: 0 auto;
            text-align: left;
            border-spacing: 0px;
            border-collapse: collapse;
            padding: 15px 0;
        }

        @media (min-width: 1281px) {
            .table-invoice-date {
                width: 1280px;
            }
        }

        .table-invoice-date tbody tr .table-invoice-left {
            padding: 10px 5px;
        }

        .table-invoice-date tbody tr .table-invoice-left div {
            padding: 5px 0;
        }

        .table-invoice-date tbody tr .table-invoice-right {
            padding: 10px 5px;
            text-align: right;
        }

        .table-bill-form-area {
            width: 100%;
            margin: 30px auto;
            text-align: left;
            border-spacing: 0px;
            border-collapse: collapse;
            padding: 15px 0;
        }

        @media (min-width: 1281px) {
            .table-bill-form-area {
                width: 1280px;
            }
        }

        .table-bill-form-area thead {
            background-color: #F2F2F2;
        }

        .table-bill-form-area thead tr th {
            padding: 15px;
        }

        .table-bill-form-area tr td {
            padding: 15px 15px 0 15px;
        }

        .bill-calculate-area {
            width: 100%;
            margin: 0 auto;
            text-align: left;
            border-spacing: 0px;
            border-collapse: collapse;
            padding: 15px 0;
        }

        @media (min-width: 1281px) {
            .bill-calculate-area {
                width: 1280px;
            }
        }

        .bill-calculate-area thead {
            background-color: #F2F2F2;
        }

        .bill-calculate-area thead tr {
            border: 0px;
        }

        .bill-calculate-area thead tr th {
            padding: 15px;
        }

        .bill-calculate-area tbody tr {
            border: 2px solid #E5E5E5;
        }

        .bill-calculate-area tbody tr .total-price {
            font-weight: bold;
        }

        .bill-calculate-area tbody tr td {
            padding: 7px 15px 7px 15px;
        }

        .bill-calculate-area tbody tr td .item-sub-cotent {
            font-size: 14px;
            color: #676767;
        }

        .bill-calculate-area .vat-discount-row {
            border: 0px solid #E5E5E5;
        }

        .bill-calculate-area .vat-discount-row .border-bottom {
            border-bottom: 2px solid #E5E5E5 !important;
        }

        .bill-calculate-area .vat-discount-row td {
            padding: 7px 15px 7px 15px;
            border-top: 0px solid #E5E5E5;
        }

        .bill-calculate-area .vat-discount-row td:nth-child(01) {
            border-left: 0px solid #E5E5E5;
        }

        .bill-calculate-area .vat-discount-row td:nth-last-child(01) {
            border-right: 0px solid #E5E5E5;
        }

        .bill-calculate-area .vat-discount-row td .item-sub-cotent {
            font-size: 14px;
            color: #676767;
        }

        .bill-calculate-area tfoot tr {
            border: 2px solid #E5E5E5;
        }

        .bill-calculate-area tfoot tr .total-price {
            font-weight: bold;
        }

        .bill-calculate-area tfoot tr td {
            padding: 7px 15px 7px 15px;
        }

        .bill-calculate-area tfoot tr td .item-sub-cotent {
            font-size: 14px;
            color: #676767;
        }

        .bill-calculate-area tfoot.last-empty-field {
            border: 0px;
        }

        .bill-calculate-area tfoot .last-amount {
            background-color: #F2F2F2;
        }

        .bill-calculate-area tfoot .last-amount:nth-child(2) {
            border: none;
            border-bottom: 2px solid #E5E5E5;
            border-left: 2px solid #E5E5E5;
        }

        .bill-calculate-area tfoot .last-amount:nth-child(3) {
            border: none;
            border-bottom: 2px solid #E5E5E5;
            border-right: 2px solid #E5E5E5;
        }

        .footers {
            width: 100%;
            margin: 30px auto;
            text-align: left;
            border-spacing: 10px;
            border-collapse: collapse;
            padding: 15px 0;
            background-color: #D8F3DC;
        }

        @media (min-width: 1281px) {
            .footers {
                width: 1280px;
            }
        }

        .footers tbody tr td {
            text-align: right;
            padding: 10px 10px;
            font-weight: 600;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
        }

        .footers tbody tr td a {
            text-decoration: none;
            color: black;
        }

        .footers tbody tr td a .fa-phone-alt {
            color: #00B553;
            margin-right: 5px;
        }

        .inline {
            display: inline;
        }

        .v-center {
            vertical-align: center;
        }
    </style>
</head>
<body>
<!--header area start-->

<table class="headers">
    <tbody class="container" style="height: 100px">
    <tr>
        {{--        <td>--}}
        {{--            <div class="inline v-center">--}}
        {{--                <img src="https://www.smanager.xyz/wp-content/uploads/2021/01/fav-icon.png" alt="n/a" class="inline">--}}
        {{--                <span class="v-center">--}}
        {{--                    <strong>{{$payment_receiver['name']}}</strong>--}}
        {{--                    <span>{{$payment_receiver['mobile']}}</span>--}}
        {{--                </span>--}}
        {{--            </div>--}}
        {{--        </td>--}}
        <td class="header-right">
            <img src="{{$payment_receiver['image']}}" style="width:40px;height:40px" alt="n/a">
        </td>
    </tr>
    </tbody>
</table>
<!-- <header class="header">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center">
      <div class="sr-company">
        <div class="d-flex align-items-center">
          <img src="assets/images/01.jpg" alt="n/a">
          <div class="logo-content">
            <h4>SR COMPANY</h4>
            <p>+880 1833 309461</p>
          </div>
        </div>
      </div>
      <div class="s-manager">
        <img src="assets/images/01.jpg" alt="n/a">
      </div>
    </div>
  </div>
</header> -->
<!--header area end-->
<!--table-invoice-date area start-->
<table class="table-invoice-date">
    <tbody>
    <tr>
        <td class="table-invoice-left">
            <div> Order ID: # {{$order_id}}</div>

        </td>
        <td class="table-invoice-right">Date: {{$created_at}}</td>
    </tr>
    </tbody>
</table>
<!--table-invoice-date area end-->
<!--table-bill-form-area area start-->
<table class="table-bill-form-area">
    <thead>
    <tr>
        <th scope="col" style="text-align: left">Bill From</th>
        <th scope="col" style="text-align: left">Bill To</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>{{$payment_receiver['name']}}</td>
        @if(isset($user['name']))
            <td>{{$user['name']}}
            </td> @endif
    </tr>
    <tr>
        <td>{{$payment_receiver['address']}}</td>
        @if(isset($user['address']))
            <td>{{$user['address']}}</td>
        @endif
    </tr>
    <tr>
        <td>{{$payment_receiver['mobile']}}</td>
        @if(isset($user['address']))
            <td>{{$user['mobile']}}</td>
        @endif
    </tr>
    </tbody>
</table>
<!--table-bill-form-area area end-->
<!--bill-calculation-area start-->
<table class="bill-calculate-area">
    <thead>
    <tr>
        <th style="text-align: left">Item</th>
        <th style="text-align: left">Quantity</th>
        <th style="text-align: left">Unit Cost</th>
        <th style="text-align: left">Total Price</th>
    </tr>
    </thead>
    <tbody>
    @foreach($pos_order['items'] as $key=>$skus)
        <tr>
            <td>
                {{$skus->name}}
                <hr style="width:50%;text-align:left;margin-left:0">
                @if(isset($skus->details))
                    @php
                        $sku_details= json_decode($skus->details,true);
                        if (isset($sku_details->name)){
                        $sku_name=$sku_details->name;
                        }
                        else{
                            $sku_name=null;
                        }
                    @endphp
                    <div class="item-sub-cotent">{{$sku_name}}</div>
                @endif
            </td>
            <td>{{$skus->quantity}}</td>
            <hr style="width:100%;text-align:left;margin-left:0">

            <td>৳{{$skus->unit_price}}</td>
            <hr style="width:100%;text-align:left;margin-left:0">

            <td>৳{{$skus->quantity*$skus->unit_price}}</td>
            <hr style="width:100%;text-align:left;margin-left:0">

        </tr>

    @endforeach

    <tr>
        <td colspan="3">Sub Total</td>
        <td class="total-price">৳{{$pos_order['grand_total']}}</td>
    </tr>

    <tr class="vat-discount-row border-top">
        <td colspan="2"></td>
        <td>Vat</td>
        <td>৳{{$pos_order['vat']}}</td>
    </tr>
    <tr class="vat-discount-row">
        <td colspan="2"></td>
        <td>Order Discount</td>
        <td>৳{{$pos_order['order_discount']}}</td>
    </tr>
    <tr class="vat-discount-row">
        <td colspan="2"></td>
        <td>Product Discount</td>
        <td>৳{{$pos_order['product_discount']}}</td>
    </tr>
    <tr class="vat-discount-row">
        <td colspan="2"></td>
        <td>Promo</td>
        <td>৳{{$pos_order['promo_discount']}}</td>
    </tr>
    <tr class="vat-discount-row">
        <td colspan="2"></td>
        <td>Delivery Charge</td>
        <td>৳{{$pos_order['delivery_charge']}}</td>
    </tr>
    <tr class="vat-discount-row">
        <td colspan="2"></td>
        <td class="border-bottom"></td>
        <td class="border-bottom"></td>
    </tr>
    <tr class="vat-discount-row">
        <td colspan="2"></td>
        <td>Paid Amount</td>
        <td>৳{{$pos_order['paid']}}</td>
    </tr>
    <tr class="vat-discount-row">
        <td colspan="2"></td>
        <td>Due Amount</td>
        <td>৳{{$pos_order['due']}}</td>
    </tr>

    <tr class="vat-discount-row">
        <td colspan="2"></td>
        <td class="border-bottom"></td>
        <td class="border-bottom"></td>
    </tr>
    </tbody>
    <tfoot>
    <tr class="vat-discount-row">
        <td colspan="2"></td>
        <td class="last-amount">Need To Pay</td>
        @if($pos_order['due']>0)
            <td class="last-amount">৳{{$pos_order['due']}}</td>

        @else
            <td class="last-amount">৳0</td>
        @endif

    </tr>
    </tfoot>
</table>
<!--bill-calculation-area end-->
<!--table footer start-->
<br>
<br>
<br>
<table class="headers">
    <tbody class="container" style="height: 100px">
    <tr>
        <td>
            <div class="inline v-center">
                <img style="padding: 10px" src="https://www.smanager.xyz/wp-content/uploads/2021/01/fav-icon.png"
                     alt="n/a" class="inline">
            </div>
        </td>
        <td class="header-right">
            <p><span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                          class="bi bi-telephone" viewBox="0 0 16 16">
  <path
      d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.568 17.568 0 0 0 4.168 6.608 17.569 17.569 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.745 1.745 0 0 1-1.657-.459L5.482 8.062a1.745 1.745 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58L3.654 1.328zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.678.678 0 0 0 .178.643l2.457 2.457a.678.678 0 0 0 .644.178l2.189-.547a1.745 1.745 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.634 18.634 0 0 1-7.01-4.42 18.634 18.634 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877L1.885.511z"/>
</svg></span>16516
            </p>
        </td>
    </tr>
    </tbody>
</table>
<!--table footer end-->
<section class="container">

    </div>
</section>
</body>
</html>
