<?php
session_start();

require_once "config/database.php";

$database = new Database(); // Instantiate the Database class
$pdo = $database->getConnection(); // Get the PDO connection

// Fetch recent notes
$recent_notes = [];
$sql = "SELECT id, title, subject, uploader_name, created_at FROM notes ORDER BY created_at DESC LIMIT 6";
if ($stmt = $pdo->prepare($sql)) {
    if ($stmt->execute()) {
        $recent_notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($stmt);
}

?>
<?php require_once 'includes/header.php'; ?>
        <div class="px-4 sm:px-6 lg:px-8 flex flex-1 justify-center py-5">
          <div class="layout-content-container flex flex-col flex-1">
            <div class=" @container">
              <div class=" @[480px]:p-4">
                <div
                  class="flex min-h-[480px] flex-col gap-6 bg-cover bg-center bg-no-repeat @[480px]:gap-8 @[480px]:rounded-lg items-center justify-center p-4 scroll-reveal"
                  style='background-image: linear-gradient(rgba(0, 0, 0, 0.1) 0%, rgba(0, 0, 0, 0.4) 100%), url("https://w0.peakpx.com/wallpaper/322/580/HD-wallpaper-alone-in-night-alone-moon-night.jpg");'
                >
                  <div class="flex flex-col gap-2 text-center">
                    <h1
                      class="text-white text-4xl font-black leading-tight tracking-[-0.033em] @[480px]:text-5xl @[480px]:font-black @[480px]:leading-tight @[480px]:tracking-[-0.033em]"
                    >
                      Unlock Your Academic Potential
                    </h1>
                    <h2 class="text-white text-sm font-normal leading-normal @[480px]:text-base @[480px]:font-normal @[480px]:leading-normal">
                      Access a vast library of student-contributed notes, study guides, and more. Find the resources you need to excel in your courses.
                    </h2>
                  </div>
                  <div class="flex-wrap gap-3 flex justify-center">
                    <a href="notes.php"
                      class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 @[480px]:h-12 @[480px]:px-5 bg-black text-white text-sm font-bold leading-normal tracking-[0.015em] @[480px]:text-base @[480px]:font-bold @[480px]:leading-normal @[480px]:tracking-[0.015em]"
                    >
                      <span class="truncate">Search</span>
                    </a>
                    <a href="<?php echo (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) ? 'notes.php' : 'register.php'; ?>"
                      class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 @[480px]:h-12 @[480px]:px-5 bg-[#363636] text-white text-sm font-bold leading-normal tracking-[0.015em] @[480px]:text-base @[480px]:font-bold @[480px]:leading-normal @[480px]:tracking-[0.015em]"
                    >
                      <span class="truncate">Get Started</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
            <h2 class="text-white text-[22px] font-bold leading-tight tracking-[-0.015em] px-4 pb-3 pt-5">How It Works</h2>
            <div class="flex flex-col p-2 gap-3">
              <details class="flex flex-col rounded-lg border border-[#4d4d4d] bg-[#1a1a1a] px-[15px] py-[7px] group scroll-reveal" open="">
                <summary class="flex cursor-pointer items-center justify-between gap-6 py-2">
                  <p class="text-white text-sm font-medium leading-normal">How It Works</p>
                  <div class="text-white group-open:rotate-180" data-icon="CaretDown" data-size="20px" data-weight="regular">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" fill="currentColor" viewBox="0 0 256 256">
                      <path d="M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z"></path>
                    </svg>
                  </div>
                </summary>
                <p class="text-[#adadad] text-sm font-normal leading-normal pb-2">
                  Create your account to get started. Share your study materials with the community. Explore notes and resources from other students. Download the materials you
                  need to succeed.
                </p>
              </details>
            </div>
            <div class="flex flex-col gap-3 p-4 scroll-reveal">
              <div class="flex flex-1 gap-3 rounded-lg border border-[#4d4d4d] bg-neutral-800 p-4 flex-col">
                <div class="text-white" data-icon="User" data-size="24px" data-weight="regular">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256">
                    <path
                      d="M230.92,212c-15.23-26.33-38.7-45.21-66.09-54.16a72,72,0,1,0-73.66,0C63.78,166.78,40.31,185.66,25.08,212a8,8,0,1,0,13.85,8c18.84-32.56,52.14-52,89.07-52s70.23,19.44,89.07,52a8,8,0,1,0,13.85-8ZM72,96a56,56,0,1,1,56,56A56.06,56.06,0,0,1,72,96Z"
                    ></path>
                  </svg>
                </div>
                <div class="flex flex-col gap-1">
                  <h2 class="text-white text-base font-bold leading-tight">Register</h2>
                  <p class="text-[#adadad] text-sm font-normal leading-normal">Create your account to get started.</p>
                </div>
              </div>
              <div class="flex flex-1 gap-3 rounded-lg border border-[#4d4d4d] bg-neutral-800 p-4 flex-col">
                <div class="text-white" data-icon="Upload" data-size="24px" data-weight="regular">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256">
                    <path
                      d="M240,136v64a16,16,0,0,1-16,16H32a16,16,0,0,1-16-16V136a16,16,0,0,1,16-16H80a8,8,0,0,1,0,16H32v64H224V136H176a8,8,0,0,1,0-16h48A16,16,0,0,1,240,136ZM85.66,77.66,120,43.31V128a8,8,0,0,0,16,0V43.31l34.34,34.35a8,8,0,0,0,11.32-11.32l-48-48a8,8,0,0,0-11.32,0l-48,48A8,8,0,0,0,85.66,77.66ZM200,168a12,12,0,1,0-12,12A12,12,0,0,0,200,168Z"
                    ></path>
                  </svg>
                </div>
                <div class="flex flex-col gap-1">
                  <h2 class="text-white text-base font-bold leading-tight">Upload</h2>
                  <p class="text-[#adadad] text-sm font-normal leading-normal">Share your study materials with the community.</p>
                </div>
              </div>
              <div class="flex flex-1 gap-3 rounded-lg border border-[#4d4d4d] bg-neutral-800 p-4 flex-col">
                <div class="text-white" data-icon="MagnifyingGlass" data-size="24px" data-weight="regular">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256">
                    <path
                      d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"
                    ></path>
                  </svg>
                </div>
                <div class="flex flex-col gap-1">
                  <h2 class="text-white text-base font-bold leading-tight">Browse</h2>
                  <p class="text-[#adadad] text-sm font-normal leading-normal">Explore notes and resources from other students.</p>
                </div>
              </div>
              <div class="flex flex-1 gap-3 rounded-lg border border-[#4d4d4d] bg-neutral-800 p-4 flex-col">
                <div class="text-white" data-icon="DownloadSimple" data-size="24px" data-weight="regular">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256">
                    <path
                      d="M224,152v56a16,16,0,0,1-16,16H48a16,16,0,0,1-16-16V152a8,8,0,0,1,16,0v56H208V152a8,8,0,0,1,16,0Zm-101.66,5.66a8,8,0,0,0,11.32,0l40-40a8,8,0,0,0-11.32-11.32L136,132.69V40a8,8,0,0,0-16,0v92.69L93.66,106.34a8,8,0,0,0-11.32,11.32Z"
                    ></path>
                  </svg>
                </div>
                <div class="flex flex-col gap-1">
                  <h2 class="text-white text-base font-bold leading-tight">Download</h2>
                  <p class="text-[#adadad] text-sm font-normal leading-normal">Download the materials you need to succeed.</p>
                </div>
              </div>
            </div>
            <h2 class="text-white text-[22px] font-bold leading-tight tracking-[-0.015em] px-4 pb-3 pt-5">Explore Subjects</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 scroll-reveal">
              <div class="flex flex-col gap-3 pb-3">
                <div
                  class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                  style='background-image: url("https://images.unsplash.com/photo-1518676590629-3dcbd9c5a5c9");'
                ></div>
                <p class="text-white text-base font-medium leading-normal">Mathematics</p>
              </div>
              <div class="flex flex-col gap-3 pb-3">
                <div
                  class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                  style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDyMhwl2T9R82m6toK0Y7QxcwpXzC6C0qla2IdWmL3LG29f5hSxxIrJzEcf4AXsmdLXn-exxhoG88u_5qdvmuRWvJ0viUXykAAtVG9IEZoUD4jfon8-Sp7ytH2e9hkPbqst7mrLZ6FVIaKnQrvXz1WBaOeKuajEWYzjkHAcLJJ_ui-U2lBvQgID1PJRfViy9iIIT6kD2xQILlQIAwQperIUXAyVqcl97BdCy2VysA1skW5aRct8leMzGer5eL-J-ahVSMCnFLNuKCc");'
                ></div>
                <p class="text-white text-base font-medium leading-normal">Physics</p>
              </div>
              <div class="flex flex-col gap-3 pb-3">
                <div
                  class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                  style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuAL6QHzWc-vdZVaR6Qp1pkXgkzQncFmvIc-2ydsJvhBn8P2wejzhr3iTUnjDpvAApJKmsMFUXUWbn_02OVUQhLsRyHruMShOYaIvUL-ktHOoWdD-KBxuEyPoFksiFOLPe4Gq_gYQ0Y9wbiv93qEIQr8Y5vVVJTALA1Bb5eJip0WiEcXK7gH385Jpw34gr9ideJCZ7h0ifcLoYAMGa3vKV3eYHJDg-PclZLoC5YnYggaqTdtTwhsFkZeMa5PvWD8chfaPNgUAQkk3Nc");'
                ></div>
                <p class="text-white text-base font-medium leading-normal">Chemistry</p>
              </div>
              <div class="flex flex-col gap-3 pb-3">
                <div
                  class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                  style='background-image: url("https://images.unsplash.com/photo-1532187863486-abf9dbad1b69");'
                ></div>
                <p class="text-white text-base font-medium leading-normal">Biology</p>
              </div>
              <div class="flex flex-col gap-3 pb-3">
                <div
                  class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                  style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuC5D_gekDakvEI-N3Oj-dN_AMQoQ8pmuot7Fk4S-avxgJw2rCL3QwAd8nd-7iXDFgMJE1ExCkzBrD3CL27Dmv6q0yccW9z0gJ3UJAU6vo5aBB6kFHPKlsKYnRXjzJ8i6WJrElaOWE9EmXyGyjcZ2z54QzTgTZ9HoNEbDRJaltuKnZ133-LZ-JC771zTEPB2IYuLjqhmtMABx7W9Q6WRmVawZzhvTPelK6je3ENPqpnFPnYsZFZK7bsTTVzGg-r99oXDZv2YI3gnMMA");'
                ></div>
                <p class="text-white text-base font-medium leading-normal">Computer Science</p>
              </div>
              <div class="flex flex-col gap-3 pb-3">
                <div
                  class="w-full bg-center bg-no-repeat aspect-square bg-cover rounded-lg"
                  style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBnIDUUrfSnhN9AX8NfFzLp7_EZAH7DWdVMP2wkZ3KR8AMT77LVmcWEEJMk6m3ihQa1H-9TAwc36CJCE9L6PH4R1LykCZMNDDxlZZD5ka9n5EarT9U6ta9nVgi0KbcR-n6dlIEW34-WFu9AcbAcrZvyp6HANBxpgoW5FG76MlYJm5go0UFq1T7Jq5p3bCn3k9OO3H9bvbmuqWwZKNfAqT2KTsRsX62A3JTBLUMYqd8vNYksAYGj_DMwld-14NTTG2lMJKz1il4NRuk");'
                ></div>
                <p class="text-white text-base font-medium leading-normal">Engineering</p>
              </div>
            </div>
            <div class=" @container scroll-reveal">
              <div class="flex flex-col justify-end gap-6 px-2 py-8 @[480px]:gap-8 @[480px]:px-8 @[480px]:py-16">
                <div class="flex flex-col gap-2 text-center">
                  <h1
                    class="text-white tracking-light text-[32px] font-bold leading-tight @[480px]:text-4xl @[480px]:font-black @[480px]:leading-tight @[480px]:tracking-[-0.033em] max-w-[720px]"
                  >
                    Join LearnHub Today🪼
                  </h1>
                  <p class="text-white text-base font-normal leading-normal max-w-[720px">"LearnHub is like GitHub for study notes - a version-controlled, community-driven platform where students securely share, discover, and collaborate on educational materials while maintaining control over their original content."</p>
                </div>
                <div class="flex flex-1 justify-center">
                  <div class="flex justify-center">
                    <a href="register.php"
                      class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 @[480px]:h-12 @[480px]:px-5 bg-black text-white text-sm font-bold leading-normal tracking-[0.015em] @[480px]:text-base @[480px]:font-bold @[480px]:leading-normal @[480px]:tracking-[0.015em] grow"
                    >
                      <span class="truncate">Join LearnHub Today</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php require_once 'includes/footer.php'; ?>