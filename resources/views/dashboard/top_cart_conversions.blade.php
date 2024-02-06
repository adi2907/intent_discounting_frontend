@if($assoc_data !== null && count($assoc_data) > 0)
    <table class="table" id="topCartTable">
        <thead>
            <th>Data</th>
        </thead>
        <tbody>
            @foreach($assoc_data as $productId => $data)   
                @if((int) $data['conversion_rate'] > 0 && array_key_exists($productId, $products) && $products[$productId] !== null) 
                <tr>
                    <td class="p-0">
                        <div class="card conversion-card product-cart-converted">
                            <div class="card-body d-flex align-items-center">
                                <img src="{{$products[$productId]['imageSrc']}}" alt="" class="product-image">    
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
                    </td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
@endif