@extends('layouts.master-without-nav')
@section('title')
@lang('translation.Login')
@endsection
@section('content')

        <div class="auth-page">
            <div class="container-fluid p-0">
                <div class="row g-0 align-items-center">
                    <div class="col-xxl-4 col-lg-4 col-md-6">
                        <div class="row justify-content-center g-0">
                            <div class="col-xl-9">
                                <div class="p-4">
                                    <div class="card mb-0">
                                        <div class="card-body">
                                            <div class="auth-full-page-content rounded d-flex p-3 my-2">
                                                <div class="w-100">
                                                    <div class="d-flex flex-column h-100">
                                                        <div class="mb-4 mb-md-1">
                                                            <a href="{{ url('/') }}" class="d-block auth-logo">
                                                                <img src="{{ URL::asset('assets/images/logo.png')}}" alt="" height="60" class="auth-logo-dark me-start">
                                                            </a>
                                                        </div>
                                                        <div class="auth-content my-auto">
                                                            {{-- <div class="text-center">
                                                                <p class="text-muted mt-2">Sign in to continue</p>
                                                            </div> --}}
                                                            <form class="mt-4 pt-2" id="loginForm" action="{{ route('login') }}" method="POST">
                                                                @csrf
                                                                <div class="form-floating form-floating-custom mb-4">
                                                                    <input type="text" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" id="input-username" placeholder="Enter User Name" name="username">
                                                                    @error('username')
                                                                        <span class="invalid-feedback" role="alert">
                                                                            <strong>{{ $message }}</strong>
                                                                        </span>
                                                                    @enderror
                                                                    <label for="input-username">Username</label>
                                                                    <div class="form-floating-icon">
                                                                        <i data-eva="people-outline"></i>
                                                                    </div>
                                                                </div>

                                                                <div class="form-floating form-floating-custom mb-4 auth-pass-inputgroup">
                                                                    <input type="password" class="form-control pe-5 @error('password') is-invalid @enderror" name="password" id="password-input" placeholder="Enter Password (length min.6)">
                                                                    @error('password')
                                                                        <span class="invalid-feedback" role="alert">
                                                                            <strong>{{ $message }}</strong>
                                                                        </span>
                                                                    @enderror
                                                                    <button type="button" class="btn btn-link position-absolute h-100 end-0 top-0" id="password-addon">
                                                                        <i class="mdi mdi-eye-outline font-size-18 text-muted"></i>
                                                                    </button>
                                                                    <label for="input-password">Password</label>
                                                                    <div class="form-floating-icon">
                                                                        <i data-feather="lock"></i>
                                                                    </div>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <button class="btn btn-danger w-100 waves-effect waves-light" type="button" onclick="formSubmit()">Masuk</button>
                                                                </div>
                                                                @if(session('error'))
                                                                    <div class="alert alert-danger">
                                                                        {{ session('error') }}
                                                                    </div>
                                                                @endif
                                                            </form>
                                                        </div>
                                                        <div class="mt-4 text-center">
                                                            <p class="mb-0"><b>© <script>document.write(new Date().getFullYear())</script> {{ env('APP_NAME') }} </b>. by <b>{{ env('APP_AUTHOR') }}</b></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end auth full page content -->
                    </div>
                    <!-- end col -->
                    <div class="col-xxl-8 col-lg-8 col-md-6">
                        <div class="auth-bg bg-white py-md-5 p-4 d-flex">
                            <div class="bg-overlay bg-white"></div>
                            <!-- end bubble effect -->
                            <div class="row justify-content-center align-items-center">
                                <div class="col-xl-8">
                                  
                                    <div class="p-0 p-sm-4 px-xl-0 py-5">
                                        <div id="reviewcarouselIndicators" class="carousel slide auth-carousel" data-bs-ride="carousel">
                                            <div class="carousel-indicators carousel-indicators-rounded">
                                                <button type="button" data-bs-target="#reviewcarouselIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                            </div>

                                            <!-- end carouselIndicators -->
                                            <div class="carousel-inner w-50 mx-auto">
                                                <div class="carousel-item active">
                                                    <div class="mt-4">
                                                        <img src="{{ URL::asset('./assets/images/loginbg.jpg')}}" class="img-fluid" alt="">
                                                    </div>
                                                    <div class="testi-contain text-center">
                                                        <h5 class="font-size-20 mt-4">Selamat Datang!</h5>
                                                        <p class="font-size-15 text-muted mt-3 mb-0">
                                                            Jika Anda seorang agen dan ingin mengirimkan produk ke toko kami, silakan mendaftar terlebih dahulu.
                                                        </p>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            <!-- end carousel-inner -->
                                        </div>
                                        <!-- end review carousel -->
                                    </div>
                                </div>
                                <!-- Button Register -->
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal">
                                    Daftar
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container fluid -->
        </div>

        <!-- Modal Register -->
        <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="registerModalLabel">Form Registrasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="registerForm">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="company-name" class="form-label">Nama Perusahaan</label>
                                <input type="text" class="form-control" id="company-name" name="company_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="full-name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="full-name" name="fullname" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="text-muted">Length Min.6</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Daftar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @endsection
        @section('script')
            <script src="{{ URL::asset('assets/js/pages/pass-addon.init.js') }}"></script>
            <script src="{{ URL::asset('assets/js/pages/eva-icon.init.js') }}"></script>
            <script>
                $('#registerForm').on('submit', async function (e) {
                    e.preventDefault();

                    const formData = new FormData(this);

                    try {
                        const response = await fetch("{{ route('register') }}", {
                            method: "POST",
                            body: formData,
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            },
                        });

                        const result = await response.json();

                        if (result.status === "success") {
                            Swal.fire({
                                title: "Berhasil!",
                                text: result.message,
                                icon: "success",
                                confirmButtonColor: "#3b76e1",
                            }).then(() => {
                                window.location.href = result.redirect_url; // Redirect ke halaman dashboard
                            });
                        } else if (result.status === "error") {
                            Swal.fire({
                                title: "Gagal!",
                                text: result.message || "Periksa input Anda.",
                                icon: "error",
                                confirmButtonColor: "#3b76e1",
                            });
                        }
                    } catch (error) {
                        Swal.fire({
                            title: "Error!",
                            text: "Terjadi kesalahan saat memproses data.",
                            icon: "error",
                            confirmButtonColor: "#3b76e1",
                        });
                    }
                });

                $('#input-username, #password-input').on('keypress', function(e) {
                    if (e.which == 13) { // 13 adalah kode tombol enter
                        formSubmit(); // Panggil fungsi formSubmit di sini
                    }
                });
                const formSubmit = async () => {

                    const username = $('#input-username').val();
                    const password = $('#password-input').val();

                    if(username == '' || password == '') {
                        Swal.fire(
                            {
                                title: 'Error!',
                                text: 'Please enter your login information.',
                                icon: 'error',
                                showCancelButton: false,
                                confirmButtonColor: '#3b76e1',
                            }
                        )
                    } else {

                        const formData = new FormData();

                        formData.append('username', username);
                        formData.append('password', password);

                        const response = await fetch('api/getlogin',{
                            method: 'POST',
                            body: formData
                        });
                        const data = await response.json();

                        if(data.code == 0) {

                            // $("#loginForm").submit();
                            Swal.fire(
                                {
                                    title: 'Error!',
                                    text: 'User Not Found.',
                                    icon: 'error',
                                    showCancelButton: false,
                                    confirmButtonColor: '#3b76e1',
                                }
                            )

                        } else if(data.code == 401) {

                            Swal.fire(
                                {
                                    title: 'Error!',
                                    text: 'Unauthorized access. Invalid password.',
                                    icon: 'error',
                                    showCancelButton: false,
                                    confirmButtonColor: '#3b76e1',
                                }
                            )
                            
                        } else if(data.code == 409) {

                            Swal.fire(
                                {
                                    title: 'Error!',
                                    html: 'Someone has accessed your account. <br> ( ip : '+data.ip+' )',
                                    icon: 'error',
                                    showCancelButton: false,
                                    confirmButtonColor: '#3b76e1',
                                }
                            )

                        } else if(data.code == 200) {
                            $("#loginForm").submit();
                        }
                    }

                }
            </script>
        @endsection
