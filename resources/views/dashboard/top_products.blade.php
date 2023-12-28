@php $productCount = 0; @endphp
@foreach($assoc_data as $productId => $countVisit)  
@if($productCount < 5)
<div class="card visited-card top-visited-product">
    <div class="card-body d-flex align-items-center visited-card-body">
        <img src="{{$products[$productId]['imageSrc'] ?? ''}}" alt="Floral T-Shirt" class="product-image">   
        <div class="product-details">
            <h3 class="product-title" style="color:black">
                <a style="color:black;font-family:'Montserrat'" target="_blank" href="https://admin.shopify.com/store/{{explode('.', $baseShop['shop_url'])[0]}}/products/{{$productId}}">
                    {{$products[$productId]['title']}}
                </a>
            </h3>
        </div>
        <div class="visit-count-border d-flex flex-column align-items-center justify-content-center">
            <!-- <span class="visit-number">13476</span> -->
            @if(isset($assoc_data) && array_key_exists($productId, $assoc_data))
                <span class="visit-number" style="font-size: 1rem !important;">{{$countVisit}}</span>
            @else 
                <span class="visit-number">N/A</span>
            @endif
            <span class="visits-label">Visits</span>
        </div>
    </div>
</div>
@php $productCount += 1; @endphp
@endif
@endforeach