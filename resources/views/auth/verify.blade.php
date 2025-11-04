@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 mt-3">
                <div class="box">
                    <h3 class="box-title py-2 px-3">Verify Your Email Address</h3>

                    <div class="box-body">
                        @if (session('resent'))
                            <div class="alert alert-success" role="alert">A fresh verification link has been sent to
                                your email address
                            </div>
                        @endif
                        <p>Before proceeding, please check your email for a verification link.If you did not receive
                            the email,</p>
                            <a href="#"
                               id="resend-link">
                                click here to request another.
                            </a>
                            <form id="resend-form" action="{{ route('verification.resend') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_scripts')
    <script @cspNonce>
        document.addEventListener('DOMContentLoaded', () => {
            const resendLink = document.getElementById('resend-link');
            const resendForm = document.getElementById('resend-form');
            if (resendLink && resendForm) {
                resendLink.addEventListener('click', (event) => {
                    event.preventDefault();
                    resendForm.submit();
                });
            }
        });
    </script>
@endpush
