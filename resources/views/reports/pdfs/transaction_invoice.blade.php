<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous">

    <title>Hello, world!</title>
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
        border: 0px;
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
        border: 0px;
    }

    .bill-calculation-area table .vat-discount-row .border-bottom {
        border-bottom: 2px solid #E5E5E5 !important;
    }

    .bill-calculation-area table tfoot.last-empty-field {
        border: 0px;
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
</style>
<body>
<header class="header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div class="sr-company">
                <div class="d-flex align-items-center">
                    <img src="https://fberialt.sirv.com/WP_www.smanager.xyz/2021/01/fav-icon.png" alt="n/a">
                    <div class="logo-content">
                        <h4>SR COMPANY</h4>
                        <p>+880 1833 309461</p>
                    </div>
                </div>
            </div>
            <div class="s-manager">
                <img src="https://fberialt.sirv.com/WP_www.smanager.xyz/2021/01/fav-icon.png" alt="n/a">
            </div>
        </div>
    </div>
</header>

<!-- Optional JavaScript; choose one of the two! -->

<!-- Option 1: Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-U1DAWAznBHeqEIlVSCgzq+c9gqGAJn5c/t99JyeKa9xxaYpSvHU5awsuZVVFIhvj" crossorigin="anonymous"></script>

<!-- Option 2: Separate Popper and Bootstrap JS -->
<!--
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js" integrity="sha384-eMNCOe7tC1doHpGoWe/6oMVemdAVTMs2xqW4mwXrXsW0L84Iytr2wi5v2QjrP/xp" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js" integrity="sha384-cn7l7gDp0eyniUwwAZgrzD06kc/tftFf19TOAs2zVinnD/C7E91j9yyk5//jjpt/" crossorigin="anonymous"></script>
-->
</body>
</html>
