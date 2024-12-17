<body class="p-4 text-gray-800 bg-gray-50">
    <!-- School Header -->
    <header class="text-center">
      <div class="max-w-[1200px] mx-auto">
        <h1 class="text-2xl font-bold uppercase md:text-3xl">
          Rolex Comprehensive College
        </h1>
        <p class="mt-1 text-sm md:text-base">
          121/4 Nwaogbe Crescent, Off Abanise Bus-Stop, Iba New Site, Ojo Lagos
        </p>
        <p class="text-xs md:text-sm">
          Tel: 08061365630, 0803710761 | Email:
          <a href="mailto:rolexschoolslagos@gmail.com" class="text-blue-600"
            >rolexschoolslagos@gmail.com</a
          >
        </p>
        <p class="text-xs md:text-sm">Website: rolexschools.com</p>
        <p class="text-xs italic font-bold md:text-sm">
          Continuous Assessment Report 2015/2016
        </p>
      </div>
    </header>

    <!-- Student Details Section -->
    <section class="grid md:grid-cols-2 gap-4 pb-4 mb-4 max-w-[1200px] mx-auto">
      <!--STUDENT'S PERSONAL DATA  -->

      <div
        class="relative w-full text-sm border border-collapse border-gray-400 md:text-base md:row-span-2"
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
            <td class="border py-[0.1rem] px-[0.5rem]">{{$record->name}}</td>
          </tr>
          <!-- SECOND ROW -->
          <tr class="px-3 text-left">
            <th class="border py-[0.1rem] px-[0.5rem]">Spin:</th>
            <td class="border py-[0.1rem] px-[0.5rem]">--</td>
          </tr>
          <!-- THIRD ROW -->
          <tr class="px-3 text-left">
            <th class="border py-[0.1rem] px-[0.5rem]">Admission NO:</th>
            <td class="border py-[0.1rem] px-[0.5rem]">RS/15/20278</td>
          </tr>
          <!-- FORTH ROW -->
          <tr class="px-3 text-left">
            <th class="border py-[0.1rem] px-[0.5rem]">SEX:</th>
            <td class="border py-[0.1rem] px-[0.5rem]">Female</td>
          </tr>
          <!-- FIFTH ROW -->
          <tr class="px-3 text-left">
            <th class="border py-[0.1rem] px-[0.5rem]">CLASS:</th>
            <td class="border py-[0.1rem] px-[0.5rem]">{{$record->class->name}}</td>
          </tr>
          <!-- SIXTH ROW -->
          <tr class="px-3 text-left">
            <th class="border py-[0.1rem] px-[0.5rem]">BARCODE:</th>
            <td class="border py-[0.1rem] px-[0.5rem]">--</td>
          </tr>
        </table>

        <div class="absolute top-0 right-0">
        <!-- src="https://via.placeholder.com/100" -->
        <img
            src="{{ $record->avatar ? Storage::url($record->avatar) : 'https://via.placeholder.com/100' }}"
            alt="Student Photo"
            class="mx-auto border rounded-md"
        />
        </div>
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
              14 Sep 2015
            </td>
            <td class="border py-[0.1rem] px-[0.5rem] text-center">
              18 Dec 2015
            </td>
            <td class="border py-[0.1rem] px-[0.5rem] text-center">
              18 Dec 2015
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
            <td class="border py-[0.1rem] px-[0.5rem] text-center">130</td>
            <td class="border py-[0.1rem] px-[0.5rem] text-center">130</td>
            <td class="border py-[0.1rem] px-[0.5rem] text-center">0</td>
          </tr>
        </table>
      </div>
    </section>

    <!-- Academic Performance -->
    <section>
      <div class="max-w-[1200px] mx-auto">
        <!-- Header -->
        <h1
          class="text-xl font-bold text-center bg-gray-200 border border-collapse border-gray-400"
        >
          ACADEMIC PERFORMANCE
        </h1>
        <!-- Table Container -->
        <div class="overflow-x-auto">
          <table class="w-full text-sm border border-collapse border-gray-400">
            <!-- Table Header -->
            <thead>
              <tr class="bg-gray-200">
                <th class="px-2 py-1 border border-gray-400" rowspan="2">
                  SUBJECTS
                </th>
                <th class="px-2 py-1 border border-gray-400" colspan="3">
                  MARKS OBTAINED
                </th>
                <th class="px-2 py-1 border border-gray-400" rowspan="2">
                  Grades
                </th>
                <th class="px-2 py-1 border border-gray-400" rowspan="2">
                  Position
                </th>
                <th class="px-2 py-1 border border-gray-400" colspan="3">
                  TERM SUMMARY
                </th>
                <th class="px-2 py-1 border border-gray-400" rowspan="2">
                  Teacher's Comment
                </th>
                <th class="px-2 py-1 border border-gray-400" rowspan="2">
                  Sign.
                </th>
              </tr>
              <tr class="bg-gray-200">
                <!-- Marks Obtained Sub-Headers -->
                <th class="px-2 py-1 border border-gray-400">1st Term C.A</th>
                <th class="px-2 py-1 border border-gray-400">1st Term Exam</th>
                <th class="px-2 py-1 border border-gray-400">
                  1st Term Scores
                </th>

                <!-- Term Summary Sub-Headers -->
                <th class="px-2 py-1 border border-gray-400">
                  Class AVG Scores
                </th>
                <th class="px-2 py-1 border border-gray-400">
                  Class Lowest Scores
                </th>
                <th class="px-2 py-1 border border-gray-400">
                  Class Highest Scores
                </th>
              </tr>
            </thead>
            <!-- Table Body -->
            <tbody>
              <!-- Row 1 -->
              <tr>
                <td class="px-2 py-1 border border-gray-400">English</td>
                <td class="text-center border border-gray-400">35</td>
                <td class="text-center border border-gray-400">28</td>
                <td class="text-center border border-gray-400">63</td>
                <td class="text-center border border-gray-400">C4</td>
                <td class="text-center border border-gray-400">7TH</td>
                <td class="text-center border border-gray-400">61</td>
                <td class="text-center border border-gray-400">45</td>
                <td class="text-center border border-gray-400">80</td>
                <td class="text-center border border-gray-400">CREDIT</td>
                <td class="text-center border border-gray-400"></td>
              </tr>

              <!-- Add rows dynamically like above -->
              <tr>
                <td class="px-2 py-1 border border-gray-400">Mathematics</td>
                <td class="text-center border border-gray-400">18</td>
                <td class="text-center border border-gray-400">24</td>
                <td class="text-center border border-gray-400">42</td>
                <td class="text-center border border-gray-400">E8</td>
                <td class="text-center border border-gray-400">9TH</td>
                <td class="text-center border border-gray-400">46</td>
                <td class="text-center border border-gray-400">37</td>
                <td class="text-center border border-gray-400">78</td>
                <td
                  class="font-bold text-center text-green-600 border border-gray-400"
                >
                  PASS
                </td>
                <td class="text-center border border-gray-400"></td>
              </tr>

              <!-- Repeat for other subjects -->
              <!-- Note: You can keep adding rows for all subjects -->
            </tbody>
          </table>

          <!-- TOTAL SCORES -->

          <div
            class="flex justify-between p-2 text-sm text-gray-700 bg-gray-200"
          >
            <div>NO. IN CLASS: <span class="font-bold">22</span></div>
            <div>TOTAL TERM SCORE: <span class="font-bold">746</span></div>
            <div>
              POSITION: <span class="font-bold">7TH - 57.4%</span> SILVER
            </div>
          </div>
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
      <div class="mx-auto max-w-[1200px] py-6">
        <!-- Table Title -->
        <h1 class="mb-4 text-xl font-bold text-center">
          SKILLS DEVELOPMENT AND BEHAVIORAL ATTRIBUTES
        </h1>

        <!-- Table Container -->
        <div class="overflow-x-auto">
          <table class="w-full text-sm border border-collapse border-gray-400">
            <!-- Header Row -->
            <thead>
              <tr class="bg-gray-200">
                <!-- Column Headers -->
                <th
                  class="px-2 py-1 text-left border border-gray-400"
                  colspan="2"
                >
                  PERSONAL DEV.
                </th>
                <th
                  class="px-2 py-1 text-left border border-gray-400"
                  colspan="2"
                >
                  SENSE OF RESP.
                </th>
                <th
                  class="px-2 py-1 text-left border border-gray-400"
                  colspan="2"
                >
                  SOCIAL DEV.
                </th>
                <th
                  class="px-2 py-1 text-left border border-gray-400"
                  colspan="2"
                >
                  PSYCHOMOTOR (SKILLS) DEV.
                </th>
              </tr>
            </thead>

            <!-- Table Body -->
            <tbody>
              <!-- Row 1 -->
              <tr>
                <!-- PERSONAL DEV -->
                <td class="px-2 py-1 border border-gray-400">OBEDIENCE:</td>
                <td class="font-bold text-center border border-gray-400">4</td>
                <!-- SENSE OF RESP -->
                <td class="px-2 py-1 border border-gray-400">PUNCTUALITY:</td>
                <td class="font-bold text-center border border-gray-400">3</td>
                <!-- SOCIAL DEV -->
                <td class="px-2 py-1 border border-gray-400">
                  COURTESY/POLITENESS:
                </td>
                <td class="font-bold text-center border border-gray-400">3</td>
                <!-- PSYCHOMOTOR DEV -->
                <td class="px-2 py-1 border border-gray-400">
                  READING AND WRITING SKILLS:
                </td>
                <td class="font-bold text-center border border-gray-400">3</td>
              </tr>

              <!-- Row 2 -->
              <tr>
                <!-- PERSONAL DEV -->
                <td class="px-2 py-1 border border-gray-400">HONESTY:</td>
                <td class="font-bold text-center border border-gray-400">4</td>
                <!-- SENSE OF RESP -->
                <td class="px-2 py-1 border border-gray-400">NEATNESS:</td>
                <td class="font-bold text-center border border-gray-400">4</td>
                <!-- SOCIAL DEV -->
                <td class="px-2 py-1 border border-gray-400">
                  CONSIDERATIONS FOR OTHERS:
                </td>
                <td class="font-bold text-center border border-gray-400">3</td>
                <!-- PSYCHOMOTOR DEV -->
                <td class="px-2 py-1 border border-gray-400">
                  VERBAL COMMUNICATION
                </td>
                <td class="font-bold text-center border border-gray-400">3</td>
              </tr>

              <!-- Row 3 -->
              <tr>
                <!-- PERSONAL DEV -->
                <td class="px-2 py-1 border border-gray-400">SELF-CONTROL:</td>
                <td class="font-bold text-center border border-gray-400">4</td>
                <!-- SENSE OF RESP -->
                <td class="px-2 py-1 border border-gray-400">PERSEVERANCE:</td>
                <td class="font-bold text-center border border-gray-400">3</td>
                <!-- SOCIAL DEV -->
                <td class="px-2 py-1 border border-gray-400">
                  SOCIABILITY / TEAM PLAYER:
                </td>
                <td class="font-bold text-center border border-gray-400">3</td>
                <!-- PSYCHOMOTOR DEV -->
                <td class="px-2 py-1 border border-gray-400">
                  SPORT AND GAME:
                </td>
                <td class="font-bold text-center border border-gray-400">4</td>
              </tr>

              <!-- Row 4 -->
              <tr>
                <!-- PERSONAL DEV -->
                <td class="px-2 py-1 border border-gray-400">SELF-RELIANCE:</td>
                <td class="font-bold text-center border border-gray-400">3</td>
                <!-- SENSE OF RESP -->
                <td class="px-2 py-1 border border-gray-400">ATTENDANCE:</td>
                <td class="font-bold text-center border border-gray-400">3</td>
                <!-- SOCIAL DEV -->
                <td class="px-2 py-1 border border-gray-400">
                  PROMPTNESS IN COMPLETING WORK:
                </td>
                <td class="font-bold text-center border border-gray-400">4</td>
                <!-- PSYCHOMOTOR DEV -->
                <td class="px-2 py-1 border border-gray-400">
                  INQUISITIVENESS:
                </td>
                <td class="font-bold text-center border border-gray-400">4</td>
              </tr>

              <!-- Row 5 -->
              <tr>
                <!-- PERSONAL DEV -->
                <td class="px-2 py-1 border border-gray-400">
                  USE OF INITIATIVE:
                </td>
                <td class="font-bold text-center border border-gray-400">3</td>
                <!-- SENSE OF RESP -->
                <td class="px-2 py-1 border border-gray-400">ATTENTIVENESS:</td>
                <td class="font-bold text-center border border-gray-400">3</td>
                <!-- SOCIAL DEV -->
                <td class="px-2 py-1 border border-gray-400">
                  ACCEPTS RESPONSIBILITIES:
                </td>
                <td class="font-bold text-center border border-gray-400">4</td>
                <!-- PSYCHOMOTOR DEV -->
                <td class="px-2 py-1 border border-gray-400">
                  DEXTERITY (MUSICAL & ART MATERIALS):
                </td>
                <td class="font-bold text-center border border-gray-400">4</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <section class="mx-auto max-w-[1200px] py-4">
      <div
        class="w-full text-sm border border-collapse border-gray-400 md:text-base"
      >
        <table class="w-full bg-gray-200">
          <tr>
            <th class="p-1">KEYS TO RATINGS ON OBSERVABLE BEHAVIOUR</th>
          </tr>
        </table>

        <table class="w-full">
          <!-- FIRST ROW -->
          <tr class="text-left">
            <td class="border py-[0.1rem] px-[0.5rem] text-center">
              5.) Maintains an excellent degree of observable traits
            </td>
            <td class="border py-[0.1rem] px-[0.5rem] text-center">
              4.) Maintains high level of observable traits
            </td>
            <td class="border py-[0.1rem] px-[0.5rem] text-center">
              3.) Acceptable level of observable traits
            </td>
          </tr>
          <tr class="w-full text-left">
            <td class="border py-[0.1rem] px-[0.5rem] text-center">
              2.) Shows minimal regards for observable traits
            </td>
            <td class="border py-[0.1rem] px-[0.5rem] text-center">
              1.) Has no regard for observable traits
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
                Trustworthy, polite and hardworking student, but there is need
                for improvement in reading skills by engaging in reading daily
                newspapers.
              </p>
            </div>
            <div class="flex items-center justify-center mt-4 md:mt-0">
              <div class="w-32 border-t border-gray-400"></div>
              <!--  -->
              <img
                src="signature-placeholder.png"
                alt="Class Teacher Signature"
                class="h-12 ml-2"
              />
            </div>
          </div>
        </div>

        <!-- Principal's Comments -->
        <div class="p-4 border-b border-gray-300">
          <div class="flex flex-col justify-between md:flex-row">
            <div>
              <p class="text-sm font-bold">Principal's Comments:</p>
              <p class="mt-2 italic text-gray-700">PASSED</p>
            </div>
            <div class="flex items-center justify-center mt-4 md:mt-0">
              <div class="w-32 border-t border-gray-400"></div>
              <!--  -->
              <img
                src="signature-placeholder.png"
                alt="Principal Signature"
                class="h-12 ml-2"
              />
            </div>
          </div>
        </div>

        <!-- Parent's Name -->
        <div class="p-4">
          <p class="text-sm font-bold">
            Parent's Name:
            <span class="font-normal">MR / MRS KEHINDE BURAIMOH</span>
          </p>
        </div>

        <!-- Footer -->
        <div class="py-2 text-xs text-center text-gray-600 bg-gray-100">
          Powered By Edu-Pack® Solutions
        </div>
      </div>
    </section>

