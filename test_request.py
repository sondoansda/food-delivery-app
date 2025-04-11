import time
import requests
from concurrent.futures import ThreadPoolExecutor, as_completed

# Sử dụng URL đúng với route /api/restaurants
URL = "http://localhost:8080/food-delivery-app/public/restaurants?page=2"
TOTAL_REQUESTS = 1000
CONCURRENT_REQUESTS = 1000

response_times = []
failed_requests = 0

def send_request(index):
    global failed_requests
    start_time = time.time()
    try:
        # Thêm header Accept: application/json để đảm bảo nhận JSON
        headers = {"Accept": "application/json"}
        response = requests.get(URL, headers=headers, timeout=10)
        if response.status_code == 200:
            print(f"✅ #{index} Status: {response.status_code}")
            return time.time() - start_time
        else:
            print(f"❌ #{index} Status: {response.status_code} - {response.text[:100]}")
            failed_requests += 1
            return None
    except requests.exceptions.RequestException as e:
        print(f"💥 #{index} Exception: {e}")
        failed_requests += 1
        return None

# Kiểm tra URL trước
try:
    headers = {"Accept": "application/json"}
    test = requests.get(URL, headers=headers, timeout=15)
    print("🔎 Test URL:", URL)
    print("✅ Test Status:", test.status_code)
    print("🧾 Sample content:", test.text[:100])
except requests.exceptions.RequestException as e:
    print("💥 Failed to connect:", e)

# Chạy test
start = time.time()
with ThreadPoolExecutor(max_workers=CONCURRENT_REQUESTS) as executor:
    futures = {executor.submit(send_request, i + 1): i + 1 for i in range(TOTAL_REQUESTS)}
    for future in as_completed(futures):
        duration = future.result()
        if duration is not None:
            response_times.append(duration)
end = time.time()

# Tổng hợp kết quả
total_time = end - start
avg_time = sum(response_times) / len(response_times) if response_times else 0
min_time = min(response_times) if response_times else 0
max_time = max(response_times) if response_times else 0

print("\n📊 Test Summary:")
print({
    "Total Requests": TOTAL_REQUESTS,
    "Successful Requests": len(response_times),
    "Failed Requests": failed_requests,
    "Total Test Duration (s)": round(total_time, 2),
    "Average Response Time (s)": round(avg_time, 3),
    "Min Response Time (s)": round(min_time, 3),
    "Max Response Time (s)": round(max_time, 3),
})