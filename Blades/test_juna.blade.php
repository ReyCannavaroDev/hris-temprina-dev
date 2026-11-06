@if(!$req->has('id'))
@verbatim
<div class="bg-white p-6 rounded-xl h-[570px]">
  <Writer @input="$log('halo')" />
  <h1>Camera Capture</h1>
  <video ref="videoElement" autoplay playsinline muted></video>
  <button @click="capture">Capture</button>
  <img v-if="capturedImage" :src="capturedImage" alt="Captured Image">
</div>
@endverbatim
@else


@verbatim

<div>
  <div class="flex flex-col bg-white p-6 w-full h-full">
    <Writer :value="values.content" 
    @input="$log('halo')" />
  </div>
</div>
@endverbatim
@endif