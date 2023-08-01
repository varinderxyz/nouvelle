<html>
      <body>
         <form action='' method='post'>
              @csrf
                      @if($errors->any())
              <ul>
             @foreach($errors->all() as $error)
            <li> {{ $error }} </li>
             @endforeach
        @endif

        @if( session( 'success' ) )
             {{ session( 'success' ) }}
        @endif

             <label>Phone numbers (seperate with a comma [,])</label>
             <input type='text' name='numbers' />

            <label>Message</label>
            <textarea name='message'></textarea>

            <button type='submit'>Send!</button>
      </form>
    </body>
</html>


<br />
                        <p style="margin-left:265px">OR</p>
                        <br />
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                              <a href="{{url('/redirect')}}" class="btn btn-primary">Login with Facebook</a>
                            </div>
                        </div>

                        <hr>
<div class="form-group row mb-0">
 <div class="col-md-8 offset-md-4">
    <a href="{{ url('/auth/redirect/facebook') }}" class="btn btn-primary"><i class="fa fa-facebook"></i> Facebook</a>
</div>
</div>