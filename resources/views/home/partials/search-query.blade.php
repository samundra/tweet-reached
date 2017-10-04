<div class="col-md-12" v-if="!firstTime">
    <h2>{{ __('app.search_query_title') }}:</h2>
    <ul>
        <li v-for="link in links">
            <a href="javascript:void(0);">@{{ link.text }}</a>
        </li>
    </ul>
</div>
