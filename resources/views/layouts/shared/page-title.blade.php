<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            @isset($sub_title)
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">MAUZO FASTA</a></li>
                        <li class="breadcrumb-item">{{ $sub_title }}</li>
                        <li class="breadcrumb-item active">{{ $page_title }}</li>
                    </ol>
                </div>
            @endisset
            <h4 class="page-title">{{ $page_title }}</h4>
        </div>
    </div>
</div>
<!-- end page title -->
