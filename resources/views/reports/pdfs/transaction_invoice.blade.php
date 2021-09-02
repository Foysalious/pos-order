<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
{{--    <link rel="stylesheet" type="text/css" href="{{ asset('CSS/style.css') }}">--}}

    <title>Invoice</title>
</head>
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
        background-color: #f9f9f9;
        counter-reset: my-sec-counter;
        scroll-behavior: smooth;
        font-family: "roboto";
    }

    .header {
        background-color: #D8F3DC;
        padding: 15px 0;
    }

    .header .sr-company .logo-content {
        padding: 0 0 0 10px;
    }

    .header .sr-company .logo-content h4 {
        margin: 0px;
    }

    .header .sr-company .logo-content p {
        margin: 0px;
        font-size: 16px;
    }

    .banner-area .banner-body {
        border: 3px solid #6fccdb;
        min-height: 100%;
    }

    .banner-area .banner-body .swiper-container {
        width: 100%;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .banner-area .banner-body .swiper-slide {
        text-align: center;
        font-size: 18px;
        background: #fff;
        /* Center slide text vertically */
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        justify-content: center;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
    }

    .banner-area .banner-body .swiper-slide img {
        display: block;
        width: auto;
        max-height: 600px;
        -o-object-fit: cover;
        object-fit: cover;
    }

    .banner-area .banner-body .swiper-pagination-fraction, .banner-area .banner-body .swiper-pagination-custom, .banner-area .banner-body .swiper-container-horizontal > .swiper-pagination-bullets {
        bottom: 10px;
        left: 0;
        width: 100%;
        position: absolute;
    }

    .banner-area .banner-body .swiper-pagination-bullet {
        width: 18px;
        height: 8px;
        display: inline-block;
        border-radius: 5px;
        background: #000;
        opacity: 0.2;
    }

    .banner-area .banner-body .swiper-pagination-bullet.swiper-pagination-bullet-active {
        background-color: #6fccdb;
        opacity: 1;
    }

    .banner-area .banner-body .link-area {
        padding: 0 20px;
        margin: 0 0;
    }

    @media (min-width: 768px) {
        .banner-area .banner-body .link-area {
            margin: 200px 0;
        }
    }

    .banner-area .banner-body .link-area .header-content h1 {
        color: #797979;
    }

    .banner-area .banner-body .link-area .sub-header h5 {
        color: #525252;
    }

    .banner-area .banner-body .link-area .sub-content p {
        color: #6f6f6f;
    }

    .banner-area .banner-body .link-area .link-button {
        margin: 20px 0;
    }

    .banner-area .banner-body .link-area .link-button a {
        text-decoration: none;
        color: white;
        background-color: #6fccdb;
        font-size: 20px;
        -webkit-transition: all 0.2s ease-in-out;
        transition: all 0.2s ease-in-out;
        padding: 10px 25px;
        border: 5px;
    }

    .banner-area .banner-body .link-area .link-button a:hover {
        background-color: #525252;
    }

    .invoice-date-area {
        padding: 20px 0;
    }

    .invoice-date-area .invoice-order-area .invoice {
        padding: 0 0 10px 0;
    }

    .bill-from-to-area table thead {
        background-color: #F2F2F2;
    }

    .bill-from-to-area table thead tr th {
        padding: 15px;
    }

    .bill-from-to-area table tr td {
        padding: 15px 15px 0 15px;
    }

    .bill-calculation-area table thead {
        background-color: #F2F2F2;
    }

    .bill-calculation-area table thead tr {
        border: 0;
    }

    .bill-calculation-area table thead tr th {
        padding: 15px;
    }

    .bill-calculation-area table tr {
        border: 2px solid #E5E5E5;
    }

    .bill-calculation-area table tr .total-price {
        font-weight: bold;
    }

    .bill-calculation-area table tr td .item-sub-cotent {
        font-size: 14px;
        color: #676767;
    }

    .bill-calculation-area table .vat-discount-row {
        border: 0;
    }

    .bill-calculation-area table .vat-discount-row .border-bottom {
        border-bottom: 2px solid #E5E5E5 !important;
    }

    .bill-calculation-area table tfoot.last-empty-field {
        border: 0;
    }

    .bill-calculation-area table tfoot .last-amount {
        background-color: #F2F2F2;
    }

    .bill-calculation-area table tfoot .last-amount:nth-child(2) {
        border: none;
        border-bottom: 2px solid #E5E5E5;
        border-left: 2px solid #E5E5E5;
    }

    .bill-calculation-area table tfoot .last-amount:nth-child(3) {
        border: none;
        border-bottom: 2px solid #E5E5E5;
        border-right: 2px solid #E5E5E5;
    }

    .footer {
        background-color: #D8F3DC;
        margin: 100px 0 0 0;
        padding: 10px 0;
        font-weight: 600;
    }

    .footer a {
        text-decoration: none;
        color: black;
    }

    .footer a .fa-phone-alt {
        color: #00B553;
        margin-right: 5px;
    }

    .header .container .test{
        display: flex;
    }
</style>
<body>
<!--header area start-->
<header class="header">
    <div class="container">
        <div class="test justify-content-between align-items-center">
            <div class="sr-company">
                <div class="test align-items-center">
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
</header>
<!--header area end-->
<!--invoice area area-->
<section class="container">
    <div class="invoice-date-area">
        <div class="d-flex justify-content-between">
            <div class="invoice-order-area">
                <div class="invoice">Invoice: # ******</div>
                <div class="order">Order ID: # ******</div>
            </div>
            <div class="date-area">
                <div class="date">Date: 16th May 2021</div>
            </div>
        </div>
    </div>
    <div class="bill-from-to-area">
        <table class="table table-borderless table-hover">
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
        <table class="table table-borderless table-hover">
            <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Unit Cost</th>
                <th>Total Price</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    Cyberpunk T-shirt
                    <div class="item-sub-cotent">Black = Double XL = Cotton = Polo</div>
                </td>
                <td>1</td>
                <td>$1,000.00</td>
                <td>$1,000.00</td>
            </tr>
            <tr>
                <td>
                    Cyberpunk T-shirt
                    <div class="item-sub-cotent">Black = Double XL = Cotton = Polo</div>
                </td>
                <td>1</td>
                <td>$1,000.00</td>
                <td>$1,000.00</td>
            </tr>
            <tr>
                <td colspan="3">Sub Total</td>
                <td class="total-price">$2,000.00</td>
            </tr>

            <tr class="vat-discount-row">
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

        </table>
    </div>
</section>
<!--invoice area end-->
<!--footer area start-->
<footer class="footer">
    <div class="container">
        <div class="d-flex justify-content-end align-items-center">
            <a href="#0"> <i class="fas fa-phone-alt"></i> 16516 </a>
        </div>
    </div>
</footer>
<!--footer area end-->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
</body>
</html>
