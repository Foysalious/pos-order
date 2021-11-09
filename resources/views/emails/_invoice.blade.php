<?php use App\Http\Resources\OrderSkuResource;use App\Services\Order\PriceCalculation;use Illuminate\Support\Facades\App;
$items = $order->orderSkus;
$price_calculator = (App::make(PriceCalculation::class))->setOrder($order)?>

<thead>
<tr>
    <th class="desc">PRODUCT NAME</th>
    <th class="qty" style="background-color: #DDDDDD;">QTY</th>
    <th class="qty">UNIT PRICE</th>
    <th class="unit">PRICE</th>
</tr>
</thead>

<tbody>

@forelse($order_info as $item)
    <tr>
        <td class=""><h3>{{ $item['name'] }}</h3></td>
        <td class="qty" style="background-color: #DDDDDD;"> {{ $item['quantity'] }} </td>
        <td class="qty"> {{ $item['unit_discounted_price_without_vat'] }} </td>
        <td class="unit"> {{ $item['quantity']*$item['unit_discounted_price_without_vat'] }} </td>
    </tr>
@empty
    <tr>
        <td class="text-center" colspan="4"> No Service Found.</td>
    </tr>
@endforelse
</tbody>
<tfoot>
<tr>
    <td colspan="2"></td>
    <td class="text-left" colspan="1">TOTAL PRICE</td>
    <td class="s-price">{{ $price_calculator->getDiscountedPriceWithoutVat() }}</td>
</tr>

@if($price_calculator->getVat() > 0)
    <tr>
        <td colspan="2"></td>
        <td class="text-left" colspan="1">VAT</td>
        <td class="s-price">{{ $price_calculator->getVat() }}</td>
    </tr>
@endif

@if($price_calculator->getOrderDiscount() > 0)
    <tr>
        <td colspan="2"></td>
        <td class="text-left" colspan="1">DISCOUNT</td>
        <td class="s-price">{{ $price_calculator->getOrderDiscount() }}</td>
    </tr>
@endif

<tr>
    <td colspan="2"></td>
    <td class="text-left" colspan="1">SUBTOTAL</td>
    <td class="s-price">{{ $price_calculator->getDiscountedPriceWithoutVat() +$price_calculator->getVat()}}</td>
</tr>
<tr>
    <td colspan="2"></td>
    <td class="text-left" colspan="1">PAID AMOUNT</td>
    <td class="s-price">{{ $price_calculator->getPaid() }}</td>
</tr>

<tr>
    <td colspan="2"></td>
    <td class="text-left" colspan="1">DUE AMOUNT</td>
    <td class="s-price">{{ $price_calculator->getDue() }}</td>
</tr>
</tfoot>
