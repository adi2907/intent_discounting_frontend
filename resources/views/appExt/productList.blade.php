@if($products !== null && $products->count() > 0)
<div class="collection__title title-wrapper title-wrapper--no-top-margin page-width collection__title--desktop-slider">
    <h2 class="title inline-richtext h1"><strong>{{$title}}</strong></h2>
</div>
<slider-component class="slider-mobile-gutter page-width slider-component-desktop">    
    <ul id="most_viewed_alme_dipak" class="grid product-grid contains-card contains-card--product contains-card--standard grid--3-col-desktop grid--2-col-tablet-down slider slider--desktop" role="list" aria-label="Slider">
        @foreach($products as $product)
        <li id="Slide-template--19392271745334__featured_collection-1" class="grid__item slider__slide">
            <div class="card-wrapper product-card-wrapper underline-links-hover">
                <div class=" card card--standard card--media   " style="--ratio-percent: 125.0%;">
                    <div class="card__inner color-background-2 gradient ratio" style="--ratio-percent: 125.0%;">
                        <div class="card__media">
                            <div class="media media--transparent media--hover-effect">
                                <img src="{{$product->imageSrc}}" sizes="(min-width: 1600px) 367px, (min-width: 990px) calc((100vw - 130px) / 4), (min-width: 750px) calc((100vw - 120px) / 3), calc((100vw - 35px) / 2)" alt="product.title" class="motion-reduce" loading="lazy" width="1600" height="1600">  
                            </div>
                        </div>
                        <div class="card__content">       
                            <div class="card__information">     
                                <h3 class="card__heading">
                                    <a href="products/{{$product->handle}}" id="StandardCardNoMediaLink-template--19392271745334__featured_collection-8404321960246" class="full-unstyled-link" aria-labelledby="StandardCardNoMediaLink-template--19392271745334__featured_collection-8404321960246 NoMediaStandardBadge-template--19392271745334__featured_collection-8404321960246">
                                        {{$product->title}}
                                    </a>   
                                </h3>  
                            </div>    
                            <div class="card__badge bottom left"></div>     
                        </div>                    
                    </div>
                    <div class="card__content">   
                        <div class="card__information">
                            <h3 class="card__heading h5" id="title-template--19392271745334__featured_collection-8404321960246">
                                <a href="products/{{$product->handle}}" class="CardLink-template--19392271745334__featured_collection-8404321960246" class="full-unstyled-link" aria-labelledby="CardLink-template--19392271745334__featured_collection-8404321960246 Badge-template--19392271745334__featured_collection-8404321960246" style="color: black; text-decoration: none; ">
                                    {{$product->title}}
                                </a>       
                            </h3>                   
                            <div class="card-information">
                                <span class="caption-large light"></span>   
                                <div class="price ">       
                                    <div class="price__container">
                                        <div class="price__regular">     
                                            <span class="visually-hidden visually-hidden--inline">Regular price</span> 
                                            <span class="price-item price-item--regular">Rs. {{$product->price}} </span>  
                                        </div>  
                                    </div>
                                </div>    
                            </div>    
                        </div>   
                    </div>  
                </div>
            </div>
        </li>
        @endforeach
    </ul>
    <div class="slider-buttons no-js-hidden">     
        <button type="button" class="slider-button slider-button--prev" name="previous" aria-label="Slide left" aria-controls="Slider-template--19392271745334__featured_collection">                
            <svg aria-hidden="true" focusable="false" class="icon icon-caret" viewBox="0 0 10 6">                
                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.354.646a.5.5 0 00-.708 0L5 4.293 1.354.646a.5.5 0 00-.708.708l4 4a.5.5 0 00.708 0l4-4a.5.5 0 000-.708z" fill="currentColor">                

                </path>
            </svg>           
        </button>            
        <div class="slider-counter caption">                
            <span class="slider-counter--current">1</span>                
            <span aria-hidden="true"> / </span>                
            <span class="visually-hidden">of</span>                
            <span class="slider-counter--total">{{$products->count()}}</span>            
        </div>            
        <button type="button" class="slider-button slider-button--next" name="next" aria-label="Slide right" aria-controls="Slider-template--19392271745334__featured_collection" disabled="disabled">                
            <svg aria-hidden="true" focusable="false" class="icon icon-caret" viewBox="0 0 10 6">                    
                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.354.646a.5.5 0 00-.708 0L5 4.293 1.354.646a.5.5 0 00-.708.708l4 4a.5.5 0 00.708 0l4-4a.5.5 0 000-.708z" fill="currentColor">                    

                </path>
            </svg>            
        </button>        
    </div>
</slider-component>  
@endif