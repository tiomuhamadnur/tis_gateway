<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>tis_gateway API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://localhost:8000";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.9.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.9.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-endpoints" class="tocify-header">
                <li class="tocify-item level-1" data-unique="endpoints">
                    <a href="#endpoints">Endpoints</a>
                </li>
                                    <ul id="tocify-subheader-endpoints" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="endpoints-GETapi-user">
                                <a href="#endpoints-GETapi-user">GET api/user</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-failures">
                                <a href="#endpoints-POSTapi-failures">POST api/failures</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-failures">
                                <a href="#endpoints-GETapi-failures">GET api/failures</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-failures--sessionId-">
                                <a href="#endpoints-GETapi-failures--sessionId-">GET api/failures/{sessionId}</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-files">
                                <a href="#endpoints-POSTapi-files">POST api/files</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-dashboard">
                                <a href="#endpoints-GETapi-dashboard">GET api/dashboard</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-analytics-trend">
                                <a href="#endpoints-GETapi-analytics-trend">GET api/analytics/trend</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-analytics-pareto">
                                <a href="#endpoints-GETapi-analytics-pareto">GET api/analytics/pareto</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-health">
                                <a href="#endpoints-GETapi-health">GET api/health</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: May 8, 2026</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>To authenticate requests, include an <strong><code>Authorization</code></strong> header with the value <strong><code>"Bearer {YOUR_AUTH_KEY}"</code></strong>.</p>
<p>All authenticated endpoints are marked with a <code>requires authentication</code> badge in the documentation below.</p>
<p>You can retrieve your token from your <code>.env</code> file (<code>TIS_API_KEY</code>).</p>

        <h1 id="endpoints">Endpoints</h1>

    

                                <h2 id="endpoints-GETapi-user">GET api/user</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-user">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/user" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/user"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-user">
            <blockquote>
            <p>Example response (500):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Server Error&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-user" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-user"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-user"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-user" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-user">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-user" data-method="GET"
      data-path="api/user"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-user', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-user"
                    onclick="tryItOut('GETapi-user');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-user"
                    onclick="cancelTryOut('GETapi-user');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-user"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/user</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-user"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-user"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-user"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-POSTapi-failures">POST api/failures</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-failures">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/failures" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"rake_id\": \"consequatur\",
    \"records\": [
        {
            \"timestamp\": \"2026-05-08T08:07:03\",
            \"equipment_name\": \"consequatur\",
            \"fault_name\": \"consequatur\",
            \"classification\": \"consequatur\"
        }
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/failures"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "rake_id": "consequatur",
    "records": [
        {
            "timestamp": "2026-05-08T08:07:03",
            "equipment_name": "consequatur",
            "fault_name": "consequatur",
            "classification": "consequatur"
        }
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-failures">
</span>
<span id="execution-results-POSTapi-failures" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-failures"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-failures"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-failures" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-failures">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-failures" data-method="POST"
      data-path="api/failures"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-failures', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-failures"
                    onclick="tryItOut('POSTapi-failures');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-failures"
                    onclick="cancelTryOut('POSTapi-failures');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-failures"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/failures</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-failures"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-failures"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-failures"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>rake_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="rake_id"                data-endpoint="POSTapi-failures"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>records</code></b>&nbsp;&nbsp;
<small>object[]</small>&nbsp;
 &nbsp;
 &nbsp;
<br>

            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>timestamp</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="records.0.timestamp"                data-endpoint="POSTapi-failures"
               value="2026-05-08T08:07:03"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-05-08T08:07:03</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>equipment_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="records.0.equipment_name"                data-endpoint="POSTapi-failures"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>fault_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="records.0.fault_name"                data-endpoint="POSTapi-failures"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>classification</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="records.0.classification"                data-endpoint="POSTapi-failures"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
                    </div>
                                    </details>
        </div>
        </form>

                    <h2 id="endpoints-GETapi-failures">GET api/failures</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-failures">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/failures" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/failures"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-failures">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;current_page&quot;: 1,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;session_id&quot;: &quot;42514004-fd73-44fd-a507-ffea995f5565&quot;,
            &quot;rake_id&quot;: &quot;RAKE-001&quot;,
            &quot;download_date&quot;: &quot;2026-04-26T07:40:39.000000Z&quot;,
            &quot;total_records&quot;: 18,
            &quot;status&quot;: &quot;completed&quot;,
            &quot;metadata&quot;: null,
            &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;failure_records&quot;: [
                {
                    &quot;id&quot;: 1,
                    &quot;session_id&quot;: 1,
                    &quot;timestamp&quot;: &quot;2026-04-27T05:12:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Brake System&quot;,
                    &quot;fault_name&quot;: &quot;Overheating&quot;,
                    &quot;classification&quot;: &quot;moderate&quot;,
                    &quot;description&quot;: &quot;Test failure record - QsRP5w72SNCwiLjiYdia&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 2,
                    &quot;session_id&quot;: 1,
                    &quot;timestamp&quot;: &quot;2026-04-26T21:16:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Coupling&quot;,
                    &quot;fault_name&quot;: &quot;Electrical Failure&quot;,
                    &quot;classification&quot;: &quot;heavy&quot;,
                    &quot;description&quot;: &quot;Test failure record - gOHdVEf05VLnANfhubAR&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 3,
                    &quot;session_id&quot;: 1,
                    &quot;timestamp&quot;: &quot;2026-04-26T10:09:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Brake System&quot;,
                    &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - QPtLIKb5A1qSlWc6PNvU&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 4,
                    &quot;session_id&quot;: 1,
                    &quot;timestamp&quot;: &quot;2026-04-26T08:19:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Electrical Failure&quot;,
                    &quot;classification&quot;: &quot;heavy&quot;,
                    &quot;description&quot;: &quot;Test failure record - 7L1f0bvzFmbtjjqSe2wu&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 5,
                    &quot;session_id&quot;: 1,
                    &quot;timestamp&quot;: &quot;2026-04-26T07:43:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Overheating&quot;,
                    &quot;classification&quot;: &quot;moderate&quot;,
                    &quot;description&quot;: &quot;Test failure record - C14U1IPUEPQEA0AE9P4c&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 6,
                    &quot;session_id&quot;: 1,
                    &quot;timestamp&quot;: &quot;2026-04-26T14:27:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Brake System&quot;,
                    &quot;fault_name&quot;: &quot;Electrical Failure&quot;,
                    &quot;classification&quot;: &quot;moderate&quot;,
                    &quot;description&quot;: &quot;Test failure record - ozZCv7zjqx01qQuufpNI&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 7,
                    &quot;session_id&quot;: 1,
                    &quot;timestamp&quot;: &quot;2026-04-27T05:26:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Coupling&quot;,
                    &quot;fault_name&quot;: &quot;Misalignment&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - HusM66ziSh1SgIPT0Y2B&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 2,
            &quot;session_id&quot;: &quot;2b1f5854-af53-4fff-93d7-e165cbdccbe3&quot;,
            &quot;rake_id&quot;: &quot;RAKE-002&quot;,
            &quot;download_date&quot;: &quot;2026-04-20T07:40:39.000000Z&quot;,
            &quot;total_records&quot;: 12,
            &quot;status&quot;: &quot;completed&quot;,
            &quot;metadata&quot;: null,
            &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;failure_records&quot;: [
                {
                    &quot;id&quot;: 8,
                    &quot;session_id&quot;: 2,
                    &quot;timestamp&quot;: &quot;2026-04-21T00:13:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Wear and Tear&quot;,
                    &quot;classification&quot;: &quot;heavy&quot;,
                    &quot;description&quot;: &quot;Test failure record - 7ytjvZin2FbSykzf1t2W&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 9,
                    &quot;session_id&quot;: 2,
                    &quot;timestamp&quot;: &quot;2026-04-20T18:33:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Overheating&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - regMKinJhdWieO6wDKsL&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 10,
                    &quot;session_id&quot;: 2,
                    &quot;timestamp&quot;: &quot;2026-04-20T18:50:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Misalignment&quot;,
                    &quot;classification&quot;: &quot;heavy&quot;,
                    &quot;description&quot;: &quot;Test failure record - MhhwBvxx2jYHJLUpBlLR&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 11,
                    &quot;session_id&quot;: 2,
                    &quot;timestamp&quot;: &quot;2026-04-20T22:25:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Pantograph&quot;,
                    &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - IHBvsECOrOtJbLTi7BAF&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 12,
                    &quot;session_id&quot;: 2,
                    &quot;timestamp&quot;: &quot;2026-04-21T00:22:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Engine&quot;,
                    &quot;fault_name&quot;: &quot;Overheating&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - XJnUFG2c4xVtnkwMpDjp&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 13,
                    &quot;session_id&quot;: 2,
                    &quot;timestamp&quot;: &quot;2026-04-20T12:43:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Pantograph&quot;,
                    &quot;fault_name&quot;: &quot;Electrical Failure&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - EWAQ59w607feCKFiC8fn&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 14,
                    &quot;session_id&quot;: 2,
                    &quot;timestamp&quot;: &quot;2026-04-21T06:20:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
                    &quot;classification&quot;: &quot;moderate&quot;,
                    &quot;description&quot;: &quot;Test failure record - ZMHjPo7J4SdltxRekRjj&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 15,
                    &quot;session_id&quot;: 2,
                    &quot;timestamp&quot;: &quot;2026-04-21T02:47:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
                    &quot;classification&quot;: &quot;heavy&quot;,
                    &quot;description&quot;: &quot;Test failure record - Ue8698G3sLneONds4hMQ&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 16,
                    &quot;session_id&quot;: 2,
                    &quot;timestamp&quot;: &quot;2026-04-20T20:51:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Engine&quot;,
                    &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
                    &quot;classification&quot;: &quot;heavy&quot;,
                    &quot;description&quot;: &quot;Test failure record - bVaYet5EWUhoicE8LV1v&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 3,
            &quot;session_id&quot;: &quot;187c419d-290b-4396-8690-5d2711c9f1f9&quot;,
            &quot;rake_id&quot;: &quot;RAKE-003&quot;,
            &quot;download_date&quot;: &quot;2026-04-24T07:40:39.000000Z&quot;,
            &quot;total_records&quot;: 15,
            &quot;status&quot;: &quot;completed&quot;,
            &quot;metadata&quot;: null,
            &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;failure_records&quot;: [
                {
                    &quot;id&quot;: 17,
                    &quot;session_id&quot;: 3,
                    &quot;timestamp&quot;: &quot;2026-04-24T15:57:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Engine&quot;,
                    &quot;fault_name&quot;: &quot;Overheating&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - eMvKPjtvs9mCj359nbr3&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 18,
                    &quot;session_id&quot;: 3,
                    &quot;timestamp&quot;: &quot;2026-04-24T21:23:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Pantograph&quot;,
                    &quot;fault_name&quot;: &quot;Overheating&quot;,
                    &quot;classification&quot;: &quot;heavy&quot;,
                    &quot;description&quot;: &quot;Test failure record - eRw1puePp29f1Dxf2ujQ&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 19,
                    &quot;session_id&quot;: 3,
                    &quot;timestamp&quot;: &quot;2026-04-25T03:37:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Engine&quot;,
                    &quot;fault_name&quot;: &quot;Misalignment&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - 7mxmAmk5DQr9KbfExuH6&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 20,
                    &quot;session_id&quot;: 3,
                    &quot;timestamp&quot;: &quot;2026-04-24T17:10:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Pantograph&quot;,
                    &quot;fault_name&quot;: &quot;Misalignment&quot;,
                    &quot;classification&quot;: &quot;moderate&quot;,
                    &quot;description&quot;: &quot;Test failure record - LO4iBv0yNVjDBhWLlUKo&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 21,
                    &quot;session_id&quot;: 3,
                    &quot;timestamp&quot;: &quot;2026-04-25T03:22:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Pantograph&quot;,
                    &quot;fault_name&quot;: &quot;Electrical Failure&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - rYGkiRKZiV1KQZqUuwgx&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 22,
                    &quot;session_id&quot;: 3,
                    &quot;timestamp&quot;: &quot;2026-04-24T12:08:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Brake System&quot;,
                    &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - 9y1kmSeITeoVFXn4lG8r&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 23,
                    &quot;session_id&quot;: 3,
                    &quot;timestamp&quot;: &quot;2026-04-25T02:51:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Coupling&quot;,
                    &quot;fault_name&quot;: &quot;Overheating&quot;,
                    &quot;classification&quot;: &quot;heavy&quot;,
                    &quot;description&quot;: &quot;Test failure record - o31aFRXJ2A3rmmSQP8n8&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 24,
                    &quot;session_id&quot;: 3,
                    &quot;timestamp&quot;: &quot;2026-04-24T12:08:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - AzlemmM1umGJXTFJsHlV&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 25,
                    &quot;session_id&quot;: 3,
                    &quot;timestamp&quot;: &quot;2026-04-25T06:56:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
                    &quot;classification&quot;: &quot;heavy&quot;,
                    &quot;description&quot;: &quot;Test failure record - qUFkZGOdadYJQpwsRjM7&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 26,
                    &quot;session_id&quot;: 3,
                    &quot;timestamp&quot;: &quot;2026-04-25T00:04:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Engine&quot;,
                    &quot;fault_name&quot;: &quot;Overheating&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - YV3t5lbaV4dzk5pGoCZT&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 4,
            &quot;session_id&quot;: &quot;5605edf0-f365-483f-aaa0-03a37a0fcd19&quot;,
            &quot;rake_id&quot;: &quot;RAKE-004&quot;,
            &quot;download_date&quot;: &quot;2026-05-03T07:40:39.000000Z&quot;,
            &quot;total_records&quot;: 18,
            &quot;status&quot;: &quot;completed&quot;,
            &quot;metadata&quot;: null,
            &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;failure_records&quot;: [
                {
                    &quot;id&quot;: 27,
                    &quot;session_id&quot;: 4,
                    &quot;timestamp&quot;: &quot;2026-05-04T02:46:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Overheating&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - dkydrJsdkSVloOxAGZqA&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 28,
                    &quot;session_id&quot;: 4,
                    &quot;timestamp&quot;: &quot;2026-05-03T19:22:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Coupling&quot;,
                    &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
                    &quot;classification&quot;: &quot;heavy&quot;,
                    &quot;description&quot;: &quot;Test failure record - X6ntiiXZE1CBTpNMGMhc&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 29,
                    &quot;session_id&quot;: 4,
                    &quot;timestamp&quot;: &quot;2026-05-03T18:37:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Pantograph&quot;,
                    &quot;fault_name&quot;: &quot;Overheating&quot;,
                    &quot;classification&quot;: &quot;moderate&quot;,
                    &quot;description&quot;: &quot;Test failure record - CHHnlyL8Pcpnuq1kFbwS&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 30,
                    &quot;session_id&quot;: 4,
                    &quot;timestamp&quot;: &quot;2026-05-03T15:06:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Brake System&quot;,
                    &quot;fault_name&quot;: &quot;Wear and Tear&quot;,
                    &quot;classification&quot;: &quot;moderate&quot;,
                    &quot;description&quot;: &quot;Test failure record - k5YYDtuwxwwZv3kLywlR&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 31,
                    &quot;session_id&quot;: 4,
                    &quot;timestamp&quot;: &quot;2026-05-03T14:24:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - 8pLQXE0ch9yyyyRbhISm&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 32,
                    &quot;session_id&quot;: 4,
                    &quot;timestamp&quot;: &quot;2026-05-04T02:31:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Coupling&quot;,
                    &quot;fault_name&quot;: &quot;Misalignment&quot;,
                    &quot;classification&quot;: &quot;moderate&quot;,
                    &quot;description&quot;: &quot;Test failure record - 31EaLqKzxHnHV4RtGbJB&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 33,
                    &quot;session_id&quot;: 4,
                    &quot;timestamp&quot;: &quot;2026-05-03T22:42:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Coupling&quot;,
                    &quot;fault_name&quot;: &quot;Wear and Tear&quot;,
                    &quot;classification&quot;: &quot;moderate&quot;,
                    &quot;description&quot;: &quot;Test failure record - MFBqmhfpUa36KO1k7DMG&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 34,
                    &quot;session_id&quot;: 4,
                    &quot;timestamp&quot;: &quot;2026-05-03T21:11:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Brake System&quot;,
                    &quot;fault_name&quot;: &quot;Misalignment&quot;,
                    &quot;classification&quot;: &quot;moderate&quot;,
                    &quot;description&quot;: &quot;Test failure record - U96y7tQ7NRFoEZxlPEEi&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 35,
                    &quot;session_id&quot;: 4,
                    &quot;timestamp&quot;: &quot;2026-05-03T17:09:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Pantograph&quot;,
                    &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - dFNVnHyowhjKOYLeycwU&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 36,
                    &quot;session_id&quot;: 4,
                    &quot;timestamp&quot;: &quot;2026-05-03T09:58:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Pantograph&quot;,
                    &quot;fault_name&quot;: &quot;Overheating&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - E8v810oD7Pmix10MnSIR&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 37,
                    &quot;session_id&quot;: 4,
                    &quot;timestamp&quot;: &quot;2026-05-03T08:35:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Electrical Failure&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - 3C80sZsNPvOUHbN6n8eR&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 38,
                    &quot;session_id&quot;: 4,
                    &quot;timestamp&quot;: &quot;2026-05-03T17:06:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Overheating&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - XhYL7qgAy1keCT2sJumJ&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                }
            ]
        },
        {
            &quot;id&quot;: 5,
            &quot;session_id&quot;: &quot;ff739398-7da7-47ad-8afc-94efd1d1d4e4&quot;,
            &quot;rake_id&quot;: &quot;RAKE-005&quot;,
            &quot;download_date&quot;: &quot;2026-04-26T07:40:39.000000Z&quot;,
            &quot;total_records&quot;: 19,
            &quot;status&quot;: &quot;completed&quot;,
            &quot;metadata&quot;: null,
            &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;failure_records&quot;: [
                {
                    &quot;id&quot;: 39,
                    &quot;session_id&quot;: 5,
                    &quot;timestamp&quot;: &quot;2026-04-26T23:31:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Brake System&quot;,
                    &quot;fault_name&quot;: &quot;Overheating&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - lCY4OMABts8cxUgcll2T&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 40,
                    &quot;session_id&quot;: 5,
                    &quot;timestamp&quot;: &quot;2026-04-26T08:41:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - AO237XEyygechoprGMVw&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 41,
                    &quot;session_id&quot;: 5,
                    &quot;timestamp&quot;: &quot;2026-04-27T07:12:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Electrical Failure&quot;,
                    &quot;classification&quot;: &quot;light&quot;,
                    &quot;description&quot;: &quot;Test failure record - 5TUC6lw2rXlSEXlsjsYZ&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 42,
                    &quot;session_id&quot;: 5,
                    &quot;timestamp&quot;: &quot;2026-04-26T13:55:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
                    &quot;classification&quot;: &quot;heavy&quot;,
                    &quot;description&quot;: &quot;Test failure record - 7LoTQngPozVAGZwsqjn5&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 43,
                    &quot;session_id&quot;: 5,
                    &quot;timestamp&quot;: &quot;2026-04-27T03:46:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Pantograph&quot;,
                    &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
                    &quot;classification&quot;: &quot;heavy&quot;,
                    &quot;description&quot;: &quot;Test failure record - FOghar82iqQwZFTbJtfx&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 44,
                    &quot;session_id&quot;: 5,
                    &quot;timestamp&quot;: &quot;2026-04-26T17:13:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Electrical Failure&quot;,
                    &quot;classification&quot;: &quot;heavy&quot;,
                    &quot;description&quot;: &quot;Test failure record - 8Rfm9mXeZlR45HmXPhm9&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 45,
                    &quot;session_id&quot;: 5,
                    &quot;timestamp&quot;: &quot;2026-04-26T14:08:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Coupling&quot;,
                    &quot;fault_name&quot;: &quot;Electrical Failure&quot;,
                    &quot;classification&quot;: &quot;heavy&quot;,
                    &quot;description&quot;: &quot;Test failure record - H4NsGB05If6UVlksUiOU&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
                },
                {
                    &quot;id&quot;: 46,
                    &quot;session_id&quot;: 5,
                    &quot;timestamp&quot;: &quot;2026-04-27T03:29:39.000000Z&quot;,
                    &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
                    &quot;fault_name&quot;: &quot;Electrical Failure&quot;,
                    &quot;classification&quot;: &quot;moderate&quot;,
                    &quot;description&quot;: &quot;Test failure record - NBginQ1dloD41kFW9Lp3&quot;,
                    &quot;additional_data&quot;: null,
                    &quot;created_at&quot;: &quot;2026-05-08T07:40:40.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-05-08T07:40:40.000000Z&quot;
                }
            ]
        }
    ],
    &quot;first_page_url&quot;: &quot;http://localhost:8000/api/failures?page=1&quot;,
    &quot;from&quot;: 1,
    &quot;last_page&quot;: 1,
    &quot;last_page_url&quot;: &quot;http://localhost:8000/api/failures?page=1&quot;,
    &quot;links&quot;: [
        {
            &quot;url&quot;: null,
            &quot;label&quot;: &quot;&amp;laquo; Previous&quot;,
            &quot;page&quot;: null,
            &quot;active&quot;: false
        },
        {
            &quot;url&quot;: &quot;http://localhost:8000/api/failures?page=1&quot;,
            &quot;label&quot;: &quot;1&quot;,
            &quot;page&quot;: 1,
            &quot;active&quot;: true
        },
        {
            &quot;url&quot;: null,
            &quot;label&quot;: &quot;Next &amp;raquo;&quot;,
            &quot;page&quot;: null,
            &quot;active&quot;: false
        }
    ],
    &quot;next_page_url&quot;: null,
    &quot;path&quot;: &quot;http://localhost:8000/api/failures&quot;,
    &quot;per_page&quot;: 15,
    &quot;prev_page_url&quot;: null,
    &quot;to&quot;: 5,
    &quot;total&quot;: 5
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-failures" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-failures"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-failures"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-failures" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-failures">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-failures" data-method="GET"
      data-path="api/failures"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-failures', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-failures"
                    onclick="tryItOut('GETapi-failures');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-failures"
                    onclick="cancelTryOut('GETapi-failures');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-failures"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/failures</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-failures"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-failures"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-failures"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-failures--sessionId-">GET api/failures/{sessionId}</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-failures--sessionId-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/failures/consequatur" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/failures/consequatur"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-failures--sessionId-">
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;No query results for model [App\\Models\\Session].&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-failures--sessionId-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-failures--sessionId-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-failures--sessionId-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-failures--sessionId-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-failures--sessionId-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-failures--sessionId-" data-method="GET"
      data-path="api/failures/{sessionId}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-failures--sessionId-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-failures--sessionId-"
                    onclick="tryItOut('GETapi-failures--sessionId-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-failures--sessionId-"
                    onclick="cancelTryOut('GETapi-failures--sessionId-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-failures--sessionId-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/failures/{sessionId}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-failures--sessionId-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-failures--sessionId-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-failures--sessionId-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>sessionId</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="sessionId"                data-endpoint="GETapi-failures--sessionId-"
               value="consequatur"
               data-component="url">
    <br>
<p>Example: <code>consequatur</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-POSTapi-files">POST api/files</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-POSTapi-files">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/files" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --form "rake_id=consequatur"\
    --form "file=@C:\Users\mtio\AppData\Local\Temp\phpAB7D.tmp" </code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/files"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
};

const body = new FormData();
body.append('rake_id', 'consequatur');
body.append('file', document.querySelector('input[name="file"]').files[0]);

fetch(url, {
    method: "POST",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-files">
</span>
<span id="execution-results-POSTapi-files" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-files"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-files"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-files" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-files">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-files" data-method="POST"
      data-path="api/files"
      data-authed="1"
      data-hasfiles="1"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-files', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-files"
                    onclick="tryItOut('POSTapi-files');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-files"
                    onclick="cancelTryOut('POSTapi-files');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-files"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/files</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-files"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-files"
               value="multipart/form-data"
               data-component="header">
    <br>
<p>Example: <code>multipart/form-data</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-files"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>rake_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="rake_id"                data-endpoint="POSTapi-files"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>file</code></b>&nbsp;&nbsp;
<small>file</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="file" style="display: none"
                              name="file"                data-endpoint="POSTapi-files"
               value=""
               data-component="body">
    <br>
<p>Must be a file. Must not be greater than 10240 kilobytes. Example: <code>C:\Users\mtio\AppData\Local\Temp\phpAB7D.tmp</code></p>
        </div>
        </form>

                    <h2 id="endpoints-GETapi-dashboard">GET api/dashboard</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-dashboard">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/dashboard" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/dashboard"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-dashboard">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;total_sessions&quot;: 5,
    &quot;total_records&quot;: 46,
    &quot;per_rake&quot;: [
        {
            &quot;rake_id&quot;: &quot;RAKE-001&quot;,
            &quot;count&quot;: 1
        },
        {
            &quot;rake_id&quot;: &quot;RAKE-002&quot;,
            &quot;count&quot;: 1
        },
        {
            &quot;rake_id&quot;: &quot;RAKE-003&quot;,
            &quot;count&quot;: 1
        },
        {
            &quot;rake_id&quot;: &quot;RAKE-004&quot;,
            &quot;count&quot;: 1
        },
        {
            &quot;rake_id&quot;: &quot;RAKE-005&quot;,
            &quot;count&quot;: 1
        }
    ],
    &quot;per_equipment&quot;: [
        {
            &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
            &quot;count&quot;: 18
        },
        {
            &quot;equipment_name&quot;: &quot;Pantograph&quot;,
            &quot;count&quot;: 9
        },
        {
            &quot;equipment_name&quot;: &quot;Coupling&quot;,
            &quot;count&quot;: 7
        },
        {
            &quot;equipment_name&quot;: &quot;Brake System&quot;,
            &quot;count&quot;: 7
        },
        {
            &quot;equipment_name&quot;: &quot;Engine&quot;,
            &quot;count&quot;: 5
        }
    ],
    &quot;per_classification&quot;: [
        {
            &quot;classification&quot;: &quot;heavy&quot;,
            &quot;count&quot;: 14
        },
        {
            &quot;classification&quot;: &quot;light&quot;,
            &quot;count&quot;: 21
        },
        {
            &quot;classification&quot;: &quot;moderate&quot;,
            &quot;count&quot;: 11
        }
    ],
    &quot;recent_heavy_faults&quot;: [
        {
            &quot;id&quot;: 28,
            &quot;session_id&quot;: 4,
            &quot;timestamp&quot;: &quot;2026-05-03T19:22:39.000000Z&quot;,
            &quot;equipment_name&quot;: &quot;Coupling&quot;,
            &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
            &quot;classification&quot;: &quot;heavy&quot;,
            &quot;description&quot;: &quot;Test failure record - X6ntiiXZE1CBTpNMGMhc&quot;,
            &quot;additional_data&quot;: null,
            &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
        },
        {
            &quot;id&quot;: 43,
            &quot;session_id&quot;: 5,
            &quot;timestamp&quot;: &quot;2026-04-27T03:46:39.000000Z&quot;,
            &quot;equipment_name&quot;: &quot;Pantograph&quot;,
            &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
            &quot;classification&quot;: &quot;heavy&quot;,
            &quot;description&quot;: &quot;Test failure record - FOghar82iqQwZFTbJtfx&quot;,
            &quot;additional_data&quot;: null,
            &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
        },
        {
            &quot;id&quot;: 2,
            &quot;session_id&quot;: 1,
            &quot;timestamp&quot;: &quot;2026-04-26T21:16:39.000000Z&quot;,
            &quot;equipment_name&quot;: &quot;Coupling&quot;,
            &quot;fault_name&quot;: &quot;Electrical Failure&quot;,
            &quot;classification&quot;: &quot;heavy&quot;,
            &quot;description&quot;: &quot;Test failure record - gOHdVEf05VLnANfhubAR&quot;,
            &quot;additional_data&quot;: null,
            &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
        },
        {
            &quot;id&quot;: 44,
            &quot;session_id&quot;: 5,
            &quot;timestamp&quot;: &quot;2026-04-26T17:13:39.000000Z&quot;,
            &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
            &quot;fault_name&quot;: &quot;Electrical Failure&quot;,
            &quot;classification&quot;: &quot;heavy&quot;,
            &quot;description&quot;: &quot;Test failure record - 8Rfm9mXeZlR45HmXPhm9&quot;,
            &quot;additional_data&quot;: null,
            &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
        },
        {
            &quot;id&quot;: 45,
            &quot;session_id&quot;: 5,
            &quot;timestamp&quot;: &quot;2026-04-26T14:08:39.000000Z&quot;,
            &quot;equipment_name&quot;: &quot;Coupling&quot;,
            &quot;fault_name&quot;: &quot;Electrical Failure&quot;,
            &quot;classification&quot;: &quot;heavy&quot;,
            &quot;description&quot;: &quot;Test failure record - H4NsGB05If6UVlksUiOU&quot;,
            &quot;additional_data&quot;: null,
            &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
        },
        {
            &quot;id&quot;: 42,
            &quot;session_id&quot;: 5,
            &quot;timestamp&quot;: &quot;2026-04-26T13:55:39.000000Z&quot;,
            &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
            &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
            &quot;classification&quot;: &quot;heavy&quot;,
            &quot;description&quot;: &quot;Test failure record - 7LoTQngPozVAGZwsqjn5&quot;,
            &quot;additional_data&quot;: null,
            &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
        },
        {
            &quot;id&quot;: 4,
            &quot;session_id&quot;: 1,
            &quot;timestamp&quot;: &quot;2026-04-26T08:19:39.000000Z&quot;,
            &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
            &quot;fault_name&quot;: &quot;Electrical Failure&quot;,
            &quot;classification&quot;: &quot;heavy&quot;,
            &quot;description&quot;: &quot;Test failure record - 7L1f0bvzFmbtjjqSe2wu&quot;,
            &quot;additional_data&quot;: null,
            &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
        },
        {
            &quot;id&quot;: 25,
            &quot;session_id&quot;: 3,
            &quot;timestamp&quot;: &quot;2026-04-25T06:56:39.000000Z&quot;,
            &quot;equipment_name&quot;: &quot;Wheel Assembly&quot;,
            &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
            &quot;classification&quot;: &quot;heavy&quot;,
            &quot;description&quot;: &quot;Test failure record - qUFkZGOdadYJQpwsRjM7&quot;,
            &quot;additional_data&quot;: null,
            &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
        },
        {
            &quot;id&quot;: 23,
            &quot;session_id&quot;: 3,
            &quot;timestamp&quot;: &quot;2026-04-25T02:51:39.000000Z&quot;,
            &quot;equipment_name&quot;: &quot;Coupling&quot;,
            &quot;fault_name&quot;: &quot;Overheating&quot;,
            &quot;classification&quot;: &quot;heavy&quot;,
            &quot;description&quot;: &quot;Test failure record - o31aFRXJ2A3rmmSQP8n8&quot;,
            &quot;additional_data&quot;: null,
            &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
        },
        {
            &quot;id&quot;: 18,
            &quot;session_id&quot;: 3,
            &quot;timestamp&quot;: &quot;2026-04-24T21:23:39.000000Z&quot;,
            &quot;equipment_name&quot;: &quot;Pantograph&quot;,
            &quot;fault_name&quot;: &quot;Overheating&quot;,
            &quot;classification&quot;: &quot;heavy&quot;,
            &quot;description&quot;: &quot;Test failure record - eRw1puePp29f1Dxf2ujQ&quot;,
            &quot;additional_data&quot;: null,
            &quot;created_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-05-08T07:40:39.000000Z&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-dashboard" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-dashboard"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-dashboard"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-dashboard" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-dashboard">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-dashboard" data-method="GET"
      data-path="api/dashboard"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-dashboard', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-dashboard"
                    onclick="tryItOut('GETapi-dashboard');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-dashboard"
                    onclick="cancelTryOut('GETapi-dashboard');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-dashboard"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/dashboard</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-dashboard"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-dashboard"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-dashboard"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-analytics-trend">GET api/analytics/trend</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-analytics-trend">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/analytics/trend" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"from\": \"2026-05-08T08:07:03\",
    \"to\": \"2026-05-08T08:07:03\",
    \"group_by\": \"week\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/analytics/trend"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "from": "2026-05-08T08:07:03",
    "to": "2026-05-08T08:07:03",
    "group_by": "week"
};

fetch(url, {
    method: "GET",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-analytics-trend">
            <blockquote>
            <p>Example response (500):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Server Error&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-analytics-trend" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-analytics-trend"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-analytics-trend"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-analytics-trend" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-analytics-trend">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-analytics-trend" data-method="GET"
      data-path="api/analytics/trend"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-analytics-trend', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-analytics-trend"
                    onclick="tryItOut('GETapi-analytics-trend');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-analytics-trend"
                    onclick="cancelTryOut('GETapi-analytics-trend');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-analytics-trend"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/analytics/trend</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-analytics-trend"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-analytics-trend"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-analytics-trend"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>from</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="from"                data-endpoint="GETapi-analytics-trend"
               value="2026-05-08T08:07:03"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-05-08T08:07:03</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>to</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="to"                data-endpoint="GETapi-analytics-trend"
               value="2026-05-08T08:07:03"
               data-component="body">
    <br>
<p>Must be a valid date. Example: <code>2026-05-08T08:07:03</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>group_by</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="group_by"                data-endpoint="GETapi-analytics-trend"
               value="week"
               data-component="body">
    <br>
<p>Example: <code>week</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>day</code></li> <li><code>week</code></li> <li><code>month</code></li></ul>
        </div>
        </form>

                    <h2 id="endpoints-GETapi-analytics-pareto">GET api/analytics/pareto</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-analytics-pareto">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/analytics/pareto" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/analytics/pareto"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-analytics-pareto">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">[
    {
        &quot;fault_name&quot;: &quot;Pressure Loss&quot;,
        &quot;frequency&quot;: 14,
        &quot;cumulative_percentage&quot;: 30.43
    },
    {
        &quot;fault_name&quot;: &quot;Overheating&quot;,
        &quot;frequency&quot;: 13,
        &quot;cumulative_percentage&quot;: 58.7
    },
    {
        &quot;fault_name&quot;: &quot;Electrical Failure&quot;,
        &quot;frequency&quot;: 10,
        &quot;cumulative_percentage&quot;: 80.43
    },
    {
        &quot;fault_name&quot;: &quot;Misalignment&quot;,
        &quot;frequency&quot;: 6,
        &quot;cumulative_percentage&quot;: 93.48
    },
    {
        &quot;fault_name&quot;: &quot;Wear and Tear&quot;,
        &quot;frequency&quot;: 3,
        &quot;cumulative_percentage&quot;: 100
    }
]</code>
 </pre>
    </span>
<span id="execution-results-GETapi-analytics-pareto" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-analytics-pareto"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-analytics-pareto"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-analytics-pareto" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-analytics-pareto">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-analytics-pareto" data-method="GET"
      data-path="api/analytics/pareto"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-analytics-pareto', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-analytics-pareto"
                    onclick="tryItOut('GETapi-analytics-pareto');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-analytics-pareto"
                    onclick="cancelTryOut('GETapi-analytics-pareto');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-analytics-pareto"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/analytics/pareto</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-analytics-pareto"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-analytics-pareto"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-analytics-pareto"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-health">GET api/health</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-health">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/health" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/health"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-health">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;ok&quot;,
    &quot;version&quot;: &quot;1.0.0&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-health" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-health"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-health"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-health" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-health">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-health" data-method="GET"
      data-path="api/health"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-health', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-health"
                    onclick="tryItOut('GETapi-health');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-health"
                    onclick="cancelTryOut('GETapi-health');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-health"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/health</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-health"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-health"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-health"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>
