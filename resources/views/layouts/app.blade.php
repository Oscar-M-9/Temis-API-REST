<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head id='metasadd'>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title id='seo_title'></title>

    {{-- <title>{{ config('app.name', 'De La Cruz Zelada Abogados E.I.R.L') }}</title> --}}

    {{-- SEO PRINCIPAL --}}
    <meta name="description" content="" id='seo_desprin' />
    <meta name="keywords" content="" id='seo_clavesprin' />

    {{-- SEO PAGINAS INTERNAS --}}
    <meta name="description" content="" id='seo_des' />
    <meta name="keywords" content="" id='seo_claves' />


    <link rel="icon" href="img/earth-globe-with-continents-maps.png" id='imgicon'>

    <!-- Scripts -->
    <script src="{{ asset('js/app.min.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="stylesheet" href="{{ asset('css/font-awesome/css/font-awesome.min.css') }}">

    <link href="{{ asset('css/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" crossorigin="anonymous">

    <!-- Styles -->
    <link href="{{ asset('css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/estilo_general.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/animate.min.css') }}" />
    <script src="{{ asset('js/jquery.min.js') }}"></script>
<style>
    @media (min-width: 992px){
    .navbar-expand-lg .navbar-nav .nav-link {
        padding-left: 0.35rem !important;
        padding-right: 0.35rem !important;
        font-size: 13px !important;
    }}

    @media (max-width: 1296px){
        .navbar-expand-lg .navbar-toggler {
            display: block;
        }
        .navbar-expand-lg .navbar-nav .nav-link {
            display: none;
        }
    }



    @media (min-width: 1380px){
        .otrosaccesos {
            margin-right: 80px !important;
        }
    }


    table{
        border: 1px solid #ed3237 !important;
        width: 100% !important;
    }
    table tr , table td{
        border: 1px solid #ed3237 !important;
        padding: 1ex !important;
    }
    #iFrameExp{
        margin:0;
        padding:0;
        height:100%;
        display:block;
        width:100%;
        border:none;
    }
</style>

</head>

<body style="background-color: #FFFFFF;">

    <div id="app">

        <main class="py-0">

            @yield('content')

        </main>

    </div>

    <script async defer id='keymapavue'></script>
    {{-- <script src="//maps.googleapis.com/maps/api/js?key=AIzaSyBDaeWicvigtP9xPv919E-RNoxfvC-Hqik&callback=iniciarMap"></script>  --}}
    {{--  <script>
        $(function() {
            var speechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition || window.mozSpeechRecognition || window.oSpeechRecognition || window.msSpeechRecognition;
            var speachdetetenermanual = false;
            var frasespeach = 'ir a ';
            if (!String.prototype.trim) {
                (function() {
                    var rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;
                    String.prototype.trim = function() {
                        return this.replace(rtrim, '');
                    };
                })();
            }

            var speachlimpiartxt = function(txt) {
                txt = txt || '';
                let txt1 = txt.toLowerCase().trim().replace(/\s+/gi, ' ').replace(/  /, ' ');
                txt1 = txt1.normalize("NFD").replace(/[\u0300-\u036f]/g, "");

                //txt1=txt1.replace(/(áéíóú)/)
                //rtxt1=txt1.normalize('NFD').replace(/([^n\u0300-\u036f]|n(?!\u0303(?![\u0300-\u036f])))[\u0300-\u036f]+/gi,"$1").normalize();

                let txt2 = txt1.replace(/[^a-zA-Z 0-9]+/gi, '').replace(/\s+/gi, ' ');
                let txt3 = txt2.replace(/(\r\n|\n|\r)/gm, "")
                return txt3;
            }

            var speachalternative = function() {
                var txt = [];
                var txtbtn = [];
                $('a').each(function(i, v) {
                    let txta = $(v).text() || '';
                    let txt_ = speachlimpiartxt(frasespeach + txta);
                    if (txt_ != '' && txt.includes(txt_) == false) {
                        txt.push(txt_);
                        txtbtn.push({
                            'texto': txt_,
                            'a': v
                        });
                    }
                })
                return txtbtn;
            }
            var speachtextos = speachalternative();
            var promisifiedOldGUM = function(constraints, successCallback, errorCallback) { // function a soporte a multiples navegadores.
                var getUserMedia = (navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia);
                if (!getUserMedia) {
                    return Promise.reject(new Error('getUserMedia is not implemented in this browser'));
                }
                return new Promise(function(successCallback, errorCallback) {
                    getUserMedia.call(navigator, constraints, successCallback, errorCallback);
                });
            }
            if (navigator.mediaDevices === undefined) {
                navigator.mediaDevices = {};
            }
            if (navigator.mediaDevices.getUserMedia === undefined) {
                navigator.mediaDevices.getUserMedia = promisifiedOldGUM;
            }


            //var grammar = '#JSGF V1.0; grammar colors; public <color> ='+speachtextos+';'
            var constraints = {
                audio: true
            };
            var iniciospeach = false;
            var escuchar = function(btni) {
                if (iniciospeach == false)
                    navigator.mediaDevices.getUserMedia(constraints)
                    .then(function(stream) {
                        //var speechRecognitionList = new SpeechGrammarList();
                        //speechRecognitionList.addFromString(grammar, 1);
                        recognizeronline = new speechRecognition();
                        //recognition.grammars = speechRecognitionList;
                        recognizeronline.continuous = false;
                        recognizeronline.lang = "es-US";
                        recognizeronline.interimResults = true;

                        recognizeronline.onstart = function() { // iniciar speach
                            btni.removeClass('fa-microphone-slash').addClass('fa-microphone animate__zoomIn animate__infinite');
                            btni.parent('.btnspeach').css('background-color', 'green');
                            iniciospeach = true;
                        }
                        recognizeronline.onend = function() { //detetener speach
                            if (speachdetetenermanual == false)
                                setTimeout(function() {
                                    escuchar(btni)
                                }, 1000);
                            btni.removeClass('fa-microphone  animate__zoomIn animate__infinite').addClass('fa-microphone-slash');
                            btni.parent('.btnspeach').css('background-color', 'red');
                            iniciospeach = false;
                        }

                        recognizeronline.onresult = function(event) { // resultado;
                            if (event.results && event.results.length) {
                                let text = event.results[0][0].transcript;
                                let txtresult = speachlimpiartxt(text);
                                let txt1 = text;

                                $.each(speachtextos, function(i, v) {
                                    let texto = speachlimpiartxt(v.texto);
                                    if (texto == txtresult && txtresult != 'ir a ' && txtresult != 'ir a') {
                                        var href = v.a.href || '';
                                        if($(v.a).parent('li.st02').length>0){
                                            var li=$(v.a).parent('li.st02');
                                            li.siblings('li').children('a[data-bs-toggle]').removeClass('show').siblings().removeClass('show');
                                            li.Siblings('li').removeClass('show');
                                            $(v.a).addClass('show');
                                            $(v.a).siblings().addClass('show');
                                            li.removeClass('show');
                                        }else{
                                            let newURL = document.createElement('a');
                                            newURL.href = href;
                                            document.body.appendChild(newURL);
                                            newURL.click();
                                            newURL.remove();
                                        }
                                        return;
                                    }
                                })
                                //console.log(speachtextos);
                            }
                        }
                        recognizeronline.start();
                    }).catch(function(err) {
                    });
            }

            /*$(window).hashchange(function() {
                speachtextos = speachalternative();
            })*/


            $('body').on('click', '.btnspeach', function(ev) {
                if (speachtextos.length == 0) {
                    speachtextos = speachalternative();
                }
                let btn = $(this);
                let i = $(this).children('i');
                if (i.hasClass('fa-microphone-slash')) {
                    speachdetetenermanual = false;
                    escuchar(i);
                } else {
                    i.removeClass('fa-microphone  animate__zoomIn animate__infinite').addClass('fa-microphone-slash');
                    btn.css('background-color', 'red');
                    speachdetetenermanual = true;
                }
            }).trigger('click');
        })
    </script>  --}}
</body>

</html>
