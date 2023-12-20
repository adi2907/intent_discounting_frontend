@php $conversionCount = 0; @endphp
@foreach($assoc_data as $productId => $data)   
@if((int) $data['conversion_rate'] > 0 && $conversionCount < 5) 
<div class="card conversion-card">
    <div class="card-body d-flex align-items-center">
        <img src="{{$products[$productId]['imageSrc']}}" alt="Floral T-Shirt" class="product-image">    
        <h3 class="product-title">
            <a style="color:black;font-family:'Montserrat'" target="_blank" href="https://admin.shopify.com/store/{{explode('.', $baseShop['shop_url'])[0]}}/products/{{$productId}}">
                {{$products[$productId]['title']}}
            </a>
        </h3>
        <div class="ml-auto conversion-rates">
            <div class="conversion-rate cart-conversion mr-4">
                @if(isset($assoc_data) && array_key_exists($productId, $assoc_data))
                    <span class="percentage" style="font-size: 1rem !important;">{{round($data['conversion_rate'], 2)}}%</span>
                @else 
                    <span class="percentage">N/A</span>
                @endif
            </div>
        </div>
    </div>
</div>
@php $conversionCount += 1; @endphp
@endif
@endforeach