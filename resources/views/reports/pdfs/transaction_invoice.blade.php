<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>sm manager</title>

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
            border-spacing: 0px;
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
    </style>
</head>
<body>
<!--header area start-->

<table class="headers">
    <tbody class="container">
    <tr>
        <td scope="col" class="header-left">
            <img src="https://www.smanager.xyz/wp-content/uploads/2021/01/fav-icon.png" alt="n/a">
            <div>
                <h4>{{$payment_receiver['name']}}</h4>
                <p>{{$payment_receiver['mobile']}}</p>
            </div>

        </td>
        <td scope="col" class="header-right">

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
            <div>Invoice: # ******</div>
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
        <th scope="col">Bill From</th>
        <th scope="col">Bill To</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>{{$payment_receiver['name']}}</td>
        <td>{{$user['name']}}</td>
    </tr>
    <tr>
        <td>{{$payment_receiver['address']}}</td>
        <td>{{$user['address']}}</td>
    </tr>
    <tr>
        <td>{{$payment_receiver['mobile']}}</td>
        <td>{{$user['mobile']}}</td>
    </tr>
    </tbody>
</table>
<!--table-bill-form-area area end-->
<!--bill-calculation-area start-->
<table class="bill-calculate-area">
    <thead>
    <tr>
        <th>Item</th>
        <th>Quantity</th>
        <th>Unit Cost</th>
        <th>Total Price</th>
    </tr>
    </thead>
    <tbody>
    @foreach($pos_order['items'] as $key=>$skus)
        <tr>
            <td>
                {{$skus->name}}
                @php
                    $sku_details= json_decode($skus['details'],true);
                    $sku_name=$sku_details['name'];

                @endphp
                <div class="item-sub-cotent">{{$sku_name}}</div>

            </td>
            <td>{{$skus->quantity}}</td>
            <td>৳{{$skus->unit_price}}</td>
            <td>৳{{$skus->quantity*$skus->unit_price}}</td>
        </tr>
    @endforeach

    <tr>
        <td colspan="3">Sub Total</td>
        <td class="total-price">৳{{$pos_order['total']}}</td>
    </tr>

    <tr class="vat-discount-row border-top">
        <td colspan="2"></td>
        <td>Vat</td>
        <td>৳{{$pos_order['vat']}}</td>
    </tr>
    <tr class="vat-discount-row">
        <td colspan="2"></td>
        <td>Discount</td>
        <td>৳{{$pos_order['discount']}}</td>
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
        <td class="last-amount">৳{{$pos_order['due']-$pos_order['total']}}</td>
    </tr>
    </tfoot>
</table>
<!--bill-calculation-area end-->
<!--table footer start-->
<table class="footers">
    <tbody>
    <tr>
        <td><img src=" https://cdn-shebadev.s3.ap-south-1.amazonaws.com/phone_24px.png" alt="n/a"> <a
                href="#0">16516</a href="#0"></td>
    </tr>
    </tbody>
</table>
<!--table footer end-->
<section class="container">
    <!-- <div class="invoice-date-area">
      <div class="d-flex justify-content-between">
        <div class="invoice-order-area">
          <div class="invoice">Invoice: # ******</div>
          <div class="order">Order ID: # ******</div>
        </div>
        <div class="date-area">
          <div class="date">Date: 16th May 2021</div>
        </div>
      </div>
    </div> -->
    <!-- <div class="bill-from-to-area">
      <table class="table table-hover">
        <thead>
          <tr>
            <th scope="col">Bill From</th>
            <th scope="col">Bill To</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>SR COMPANY</td>
            <td>Mionel Lessi</td>
          </tr>
          <tr>
            <td>51 Green corner, Green Road Dhanmondi Dhaka</td>
            <td>51 Green corner, Green Road Dhanmondi Dhaka</td>
          </tr>
          <tr>
            <td>+880 1833 309461</td>
            <td>+880 1833 309461</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="bill-calculation-area">
      <table class="table table-hover">
        <thead>
          <tr>
            <th >Item</th>
            <th >Quantity</th>
            <th >Unit Cost</th>
            <th >Total Price</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              Cyberpunk T-shirt
              <div class="item-sub-cotent">Black = Double XL = Cotton = Polo </div>
            </td>
            <td>1</td>
            <td>$1,000.00</td>
            <td>$1,000.00</td>
          </tr>
          <tr>
            <td>
              Cyberpunk T-shirt
              <div class="item-sub-cotent">Black = Double XL = Cotton = Polo </div>
            </td>
            <td>1</td>
            <td>$1,000.00</td>
            <td>$1,000.00</td>
          </tr>
          <tr>
            <td colspan="3">Sub Total </td>
            <td class="total-price">$2,000.00</td>
          </tr>

          <tr class="vat-discount-row border-top">
            <td colspan="2"></td>
            <td>Vat</td>
            <td>$20.00</td>
          </tr>
          <tr class="vat-discount-row">
            <td colspan="2"></td>
            <td>Discount</td>
            <td>$20.00</td>
          </tr>
          <tr class="vat-discount-row">
            <td colspan="2"></td>
            <td>Promo</td>
            <td>$20.00</td>
          </tr>
          <tr class="vat-discount-row">
            <td colspan="2"></td>
            <td>Delivery Charge</td>
            <td>$20.00</td>
          </tr>
          <tr class="vat-discount-row">
            <td colspan="2"></td>
            <td class="border-bottom"></td>
            <td class="border-bottom"></td>
          </tr>
          <tr class="vat-discount-row">
            <td colspan="2"></td>
            <td>Paid Amount</td>
            <td>$20.00</td>
          </tr>
          <tr class="vat-discount-row">
            <td colspan="2"></td>
            <td>Due Amount</td>
            <td>$20.00</td>
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
            <td class="last-amount">$20.00</td>
          </tr>
        </tfoot>

      </table> -->
    </div>
</section>
<!--invoice area end-->
<!--footer area start-->
<!-- <footer class="footer">
  <div class="container">
    <div class="d-flex justify-content-end align-items-center">
     <a href="#0"> <img src="" alt="n/a"> 16516 </a>
    </div>
  </div>
</footer> -->
<!--footer area end-->
</body>
</html>
