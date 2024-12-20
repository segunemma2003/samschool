
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="application-name" content="{{ config('app.name') }}" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Responsive Student Report Card</title>
        <style>
            [x-cloak] {
                display: none !important;
            }
            @media print {
        /* Remove unnecessary padding/margins */
        body {
          margin: 0;
          padding: 0;
          background-color: #fff;
          font-size: 10px;
        }

        /* Page size and margin */
        @page {
          size: A4 portrait;
          margin: 1cm;
        }

        /* Ensure tables fit within the page */
        /* table {
          width: 100%;
          border-collapse: collapse;
        } */

        /* th,
        td {
          border: 1px solid #000;
          padding: 4px;
          text-align: center;
        } */

        /* Hide unnecessary content on print */
        .no-print {
          display: none;
        }

        /* Ensure headers and important text are bold */
        h1,
        h2,
        h3 {
          font-size: 12px;
          font-weight: bold;
          text-align: center;
        }

        .print-title {
          text-align: center;
          margin-bottom: 10px;
        }

        /* Adjust image size for print */
        img {
          max-width: 100px;
          max-height: 100px;
          object-fit: cover;
        }
      }
        </style>

        @filamentStyles
        @vite('resources/css/app.css')
    </head>

    {{-- <body class="antialiased"> --}}
    <body class="p-4 text-gray-800 bg-gray-50">
        <header class="flex space-x-3 text-center">
            <div class="pt-[2rem]">
              <img
                width="100"
                height="100"
                src="{{ $school->school_logo ? Storage::url($school->school_logo) : 'https://via.placeholder.com/100' }}"
                alt="{{$school->school_logo}}"
                class="mx-auto rounded-md w-[100px] h-[100px] object-cover"
              />
            </div>

            <div class="w-[210mm] mx-auto text-center">
              <h1 class="text-xl font-bold uppercase md:text-3xl">
                {{$school->school_name}}
              </h1>
              <p class="mt-1 text-sm md:text-base">
                {{$school->school_address}}
              </p>
              <p class="text-xs md:text-sm">
                Tel: {{ $school->school_phone }} | Email:
                <a href="{{'mailto:'.$school->email ?? 'info@' . $school->school_website}}" class="text-blue-600"
                  >{{ $school->email ?? 'info@' . $school->school_website }}</a
                >
              </p>
              <p class="text-xs md:text-sm">Website: {{$school->school_website}}</p>
              <p class="text-xs italic font-bold md:text-sm">
                Continuous Assessment Report  {{$academy->title}}
              </p>
            </div>
    </header>

    <!-- Student Details Section -->
    <section
        class="grid grid-cols-2 gap-4 pb-4 mb-4 w-[210mm] mx-auto print:grid print:grid-cols-2 print:gap-4"
        >
      <!--STUDENT'S PERSONAL DATA  -->

      <div
        class="relative w-full text-sm border border-collapse border-gray-400 md:text-base md:row-span-2 print:row-span-2"
        >
        <table class="w-full bg-gray-200">
            <tr>
              <th class="p-1">STUDENT'S PERSONAL DATA</th>
            </tr>
          </table>

          <table class="w-full">
            <!-- FIRST ROW -->
            <tr class="text-left">
              <th class="border py-[0.1rem] px-[0.5rem]">Name:</th>
              <td class="border py-[0.1rem] px-[0.5rem]">{{$student->name}}</td>
            </tr>
            <!-- SECOND ROW -->
            <tr class="px-3 text-left">
              <th class="border py-[0.1rem] px-[0.5rem]">Spin:</th>
              <td class="border py-[0.1rem] px-[0.5rem]">--</td>
            </tr>
            <!-- THIRD ROW -->
            <tr class="px-3 text-left">
              <th class="border py-[0.1rem] px-[0.5rem]">Admission NO:</th>
              <td class="border py-[0.1rem] px-[0.5rem]">{{$student->registration_number}}</td>
            </tr>
            <!-- FORTH ROW -->
            <tr class="px-3 text-left">
              <th class="border py-[0.1rem] px-[0.5rem]">SEX:</th>
              <td class="border py-[0.1rem] px-[0.5rem]">{{$student->gender}}</td>
            </tr>
            <!-- FIFTH ROW -->
            <tr class="px-3 text-left">
              <th class="border py-[0.1rem] px-[0.5rem]">CLASS:</th>
              <td class="border py-[0.1rem] px-[0.5rem]">{{$student->class->name}}</td>
            </tr>
            <!-- SIXTH ROW -->
            <tr class="px-3 text-left">
              <th class="border py-[0.1rem] px-[0.5rem]">BARCODE:</th>
              <td class="border py-[0.1rem] px-[0.5rem]">--</td>
            </tr>
          </table>


{{-- <div class="relative">
    <div class="absolute bottom-0 right-0"> --}}
        <img
         width="40"
            height="40"
          src="{{ $student->avatar ? Storage::url($student->avatar) : 'https://via.placeholder.com/100' }}"
          alt="Student Photo"
         class="mx-auto rounded-md w-[100px] h-[100px] object-cover"
        />
      {{-- </div>
</div> --}}

        </div>

      <!--TERMINAL DURATION (........) WEEKS  -->

      <div
        class="w-full text-sm border border-collapse border-gray-400 md:text-base"
      >
        <table class="w-full bg-gray-200">
          <tr>
            <th class="p-1">ATTENDANCE</th>
          </tr>
        </table>

        <table class="w-full">
          <tr>
            <th class="border py-[0.1rem] px-[0.5rem] text-center">
              Term Begins
            </th>
            <th class="border py-[0.1rem] px-[0.5rem] text-center">
              Term Ends
            </th>
            <th class="border py-[0.1rem] px-[0.5rem] text-center">
              Next Term Begins
            </th>
          </tr>
          <!-- FIRST ROW -->
          <tr class="text-left">
            <td class="border py-[0.1rem] px-[0.5rem] text-center">
                {{$school->term_begin}}
            </td>
            <td class="border py-[0.1rem] px-[0.5rem] text-center">
                {{$school->term_ends}}
            </td>
            <td class="border py-[0.1rem] px-[0.5rem] text-center">
                {{$school->next_term_begins}}
            </td>
          </tr>
        </table>
      </div>


      <div
      class="w-full text-sm border border-collapse border-gray-400 md:text-base"
    >
      <table class="w-full bg-gray-200">
        <tr>
          <th class="p-1">TERMINAL DURATION (........) WEEKS</th>
        </tr>
      </table>

      <table class="w-full">
        <tr>
          <th class="border py-[0.1rem] px-[0.5rem] text-center">
            Times Opened:
          </th>
          <th class="border py-[0.1rem] px-[0.5rem] text-center">
            Times Present:
          </th>
          <th class="border py-[0.1rem] px-[0.5rem] text-center">
            Times Absent:
          </th>
        </tr>
        <!-- FIRST ROW -->
        <tr class="text-left">
          <td class="border py-[0.1rem] px-[0.5rem] text-center">{{$studentAttendance->expected_present}}</td>
          <td class="border py-[0.1rem] px-[0.5rem] text-center">{{$studentAttendance->total_present}}</td>
          <td class="border py-[0.1rem] px-[0.5rem] text-center">{{$studentAttendance->total_absent}}</td>
        </tr>
      </table>
    </div>
  </section>

    <!-- Academic Performance -->
    <section>
        <div class="max-w-[1200px] mx-auto px-4">
          <!-- Header -->
          <h1
            class="text-xl font-bold text-center bg-gray-200 border border-gray-400"
          >
            ACADEMIC PERFORMANCE
          </h1>
          <!-- Table Container -->
          <div class="overflow-x-auto">
            <table class="w-full text-sm border border-collapse border-gray-400 table-auto">
              <!-- Table Header -->
              <thead>
                <tr class="bg-gray-200">
                  <th class="px-2 py-1 border border-gray-400" rowspan="2">
                    SUBJECTS
                  </th>

                  <th class="px-2 py-1 border border-gray-400" colspan="{{count($markObtained)}}">
                    MARKS OBTAINED
                  </th>
                  @foreach($studentSummary as $marks)
                  <th class="px-2 py-1 border border-gray-400" rowspan="2">
                        {{ $marks->name}}
                  </th>
                  @endforeach

                  <th class="px-2 py-1 border border-gray-400" colspan="{{count($termSummary)}}">
                    TERM SUMMARY
                  </th>

                  @foreach($remarks as $marks)
                  <th class="px-2 py-1 border border-gray-400" rowspan="2">
                        {{ $marks->name}}
                  </th>
                  @endforeach
                  <th class="px-2 py-1 border border-gray-400" rowspan="2">
                    Sign.
                  </th>
                </tr>
                <tr class="bg-gray-200">

                  @foreach($markObtained as $marks)
                  <th class="px-2 py-1 border border-gray-400">
                        {{ $marks->name}}
                  </th>
                  @endforeach
                  <!-- Term Summary Sub-Headers -->
                  @foreach($termSummary as $marks)
                  <th class="px-2 py-1 border border-gray-400">
                        {{ $marks->name}}
                  </th>
                  @endforeach

                </tr>
              </thead>
              <!-- Table Body -->
              <tbody>
                @foreach($courses as $course)
                    <tr>
                        <td class="px-2 py-1 border border-gray-400">{{$course->subject->subjectDepot->name}}</td>
                        @foreach($markObtained as $heading)
                        <td class="px-2 py-1 border border-gray-400">
                            @php
                                // Retrieve the score for this subject and heading
                                $score = $course->scoreBoard->firstWhere('result_section_type_id', $heading->id);
                            @endphp
                            {{ $score->score ?? 'N/A' }}  <!-- Display the score or "N/A" if not available -->
                        </td>
                        @endforeach

                        @foreach($studentSummary as $heading)
                        <td class="px-2 py-1 border border-gray-400">
                            @php
                                // Retrieve the score for this subject and heading
                                $score = $course->scoreBoard->firstWhere('result_section_type_id', $heading->id);
                            @endphp
                            {{ $score->score ?? 'N/A' }}  <!-- Display the score or "N/A" if not available -->
                        </td>
                        @endforeach

                        @foreach($termSummary as $heading)
                        <td class="px-2 py-1 border border-gray-400">
                            @php
                                // Retrieve the score for this subject and heading
                                $score = $course->scoreBoard->firstWhere('result_section_type_id', $heading->id);
                            @endphp
                            {{ $score->score ?? 'N/A' }}  <!-- Display the score or "N/A" if not available -->
                        </td>
                        @endforeach
                        @foreach($remarks as $heading)
                        <td class="px-2 py-1 border border-gray-400">
                            @php
                                // Retrieve the score for this subject and heading
                                $score = $course->scoreBoard->firstWhere('result_section_type_id', $heading->id);
                            @endphp
                            {{ $score->score ?? 'N/A' }}  <!-- Display the score or "N/A" if not available -->
                        </td>
                        @endforeach

                        <td class="text-center border border-gray-400">
                            <img
                             width="25"
                            height="25"
                            src="{{ $course->subject->teacher->signature ? $course->subject->teacher->signature : 'https://via.placeholder.com/100' }}"
                            alt="Teacher Signature"
                            class="mx-auto rounded-md w-[100px] h-[100px] object-cover"
                          />
                        </td>
                    </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <!-- TOTAL SCORES -->
          <div
            class="flex flex-wrap justify-between p-2 text-sm text-gray-700 bg-gray-200"
          >
            {{-- <div>NO. IN CLASS: <span class="font-bold">22</span></div> --}}
            <div>TOTAL TERM SCORE: <span class="font-bold">{{$totalScore}}</span></div>
             <div>TOTAL Subject Taken: <span class="font-bold">{{$totalSubject}}</span></div>
               <div>TOTAL Percent: <span class="font-bold">{{$percent}} %</span></div>
            {{-- <div>
              POSITION: <span class="font-bold">7TH - 57.4%</span> SILVER
            </div> --}}
          </div>
        </div>
      </section>


    <section class="mx-auto max-w-[1200px] pt-6">
      <div
        class="w-full text-sm border border-collapse border-gray-400 md:text-base"
      >
        <table class="w-full bg-gray-200">
          <tr>
            <th class="p-1">GRADE</th>
          </tr>
        </table>

        <table class="w-full">
          <!-- FIRST ROW -->
          <tr class="text-left">
            <td class="border py-[0.1rem] px-[0.5rem] text-center">
              A1 75 - 100 (EXCELLENT)
            </td>
            <td class="border py-[0.1rem] px-[0.5rem] text-center">
              B2 71 - 74 (VERY GOOD)
            </td>
            <td class="border py-[0.1rem] px-[0.5rem] text-center">
              B3 65 - 70 (GOOD)
            </td>
            <td class="border py-[0.1rem] px-[0.5rem] text-center">
              C4 61 - 64 (CREDIT)
            </td>
          </tr>
        </table>
      </div>
    </section>



    <section>
      <div
        class="max-w-[1200px] mx-auto border border-gray-400 rounded-lg bg-white"
      >
        <!-- Header -->
        <div class="py-2 text-sm font-bold text-center uppercase bg-gray-300">
          Remarks and Conclusion
        </div>

        <!-- Class Teacher's Comments -->
        <div class="p-4 border-b border-gray-300">
          <div class="flex flex-col justify-between md:flex-row">
            <div>
              <p class="text-sm font-bold">Class Teacher's Comments:</p>
              <p class="mt-2 italic text-gray-700">
                {{$studentComment->comment}}
              </p>
            </div>
            <div class="flex items-center justify-center mt-4 md:mt-0">
              {{-- <div class="w-32 border-t border-gray-400"></div> --}}
              <!--  --> <img
                width="40"
              height="40"
                src="{{ $class->teacher->signature ? $class->teacher->signature : 'https://via.placeholder.com/100' }}"
                alt="{{$class->teacher->signature}}"
               class="mx-auto rounded-md w-[100px] h-[100px] object-cover"
              />

            </div>
          </div>
        </div>

        <!-- Principal's Comments -->
        <div class="p-4 border-b border-gray-300">
          <div class="flex flex-col justify-between md:flex-row">
            <div>
              <p class="text-sm font-bold">Head Teacher's/ Principal's Comment:</p>
              <p class="mt-2 italic text-gray-700">{{$principalComment}}</p>
            </div>
            <div class="flex flex-row items-center justify-center mt-4 md:mt-0 gap-3">
              {{-- <div class="w-32 border-t border-gray-400"></div> --}}
              <!--  -->
              {{-- <img
              width="40"
              height="40"
               src="{{ $school->principal_sign ? $school->principal_sign : 'https://via.placeholder.com/100' }}"
                alt="{{ $school->principal_sign}}"
                class="mx-auto rounded-md w-[100px] h-[100px] object-cover"
              /> --}}
              <img
                width="40"
              height="40"
                src="{{ $school->school_stamp ? Storage::url($school->school_stamp) : 'https://via.placeholder.com/100' }}"
                alt="{{ $school->school_stamp}}"
               class="mx-auto rounded-md w-[100px] h-[100px] object-cover"
              />
            </div>
          </div>
        </div>

        <!-- Parent's Name -->
        <div class="p-4">
          <p class="text-sm font-bold">
            Parent's Name:
            <span class="font-normal">{{$student->parent->name}}</span>
          </p>
        </div>

        <!-- Footer -->
        <div class="py-2 text-xs text-center text-gray-600 bg-gray-100">
          Powered By Compasse Africa
        </div>
      </div>
    </section>

    @livewire('notifications')

@filamentScripts
@vite('resources/js/app.js')
  </body>
</html>
