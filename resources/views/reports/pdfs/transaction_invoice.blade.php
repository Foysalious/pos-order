<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <title>Invoice</title>
    <style>

    </style>
</head>
<body>

<!--header area start-->
<header class="clearfix header">
    <table>
        <tr>
            <th style="width: 10%">Something</th>
            <th style="width: 10%">Something<br>01715096710</th>
            <th style="width: 80%">Something</th>
        </tr>
        {{--    <div>--}}
        {{--        <div class="container">--}}
        {{--            <div class="d-flex justify-content-between align-items-center">--}}
        {{--                <div class="sr-company">--}}
        {{--                    <div class="logo">--}}
        {{--                        <img src="https://www.smanager.xyz/wp-content/uploads/2021/01/fav-icon.png" alt="n/a" class="">--}}
        {{--                    </div>--}}
        {{--                    <div class="logo-content">--}}
        {{--                        <h4>SR COMPANY</h4>--}}
        {{--                        <p>+880 1833 309461</p>--}}
        {{--                    </div>--}}
        {{--                </div>--}}
        {{--                <div class="s-manager">--}}
        {{--                    <img src="https://www.smanager.xyz/wp-content/uploads/2021/01/fav-icon.png" alt="n/a">--}}
        {{--                </div>--}}
        {{--            </div>--}}
        {{--        </div>--}}
        {{--    </div>--}}
    </table>
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
