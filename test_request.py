import time
import requests
from concurrent.futures import ThreadPoolExecutor, as_completed

URL = "http://localhost:8080/food-delivery-app/public/restaurants?page=2"
TOTAL_REQUESTS = 1000
CONCURRENT_REQUESTS = 1000

response_times = []
failed_requests = 0

def send_request(index):
    global failed_requests
    start_time = time.time()
    try:
        response = requests.get(URL, timeout=10)

        if response.status_code == 200:
            print(f"✅ #{index} Status: {response.status_code}")
            return time.time() - start_time
        else:
            print(f"❌ #{index} Status: {response.status_code} ")
            failed_requests += 1
            return None
    except Exception as e:
        print(f"💥 #{index} Exception: {e}")
        failed_requests += 1
        return None

# Kiểm tra thử URL trước
try:
    test = requests.get(URL, timeout=10)
    print("🔎 Test URL:", URL)
    print("✅ Test Status:", test.status_code)
    print("🧾 Sample content:", test.text[:100])
except Exception as e:
    print("💥 Failed to connect:", e)

# Chạy test hiệu năng có đánh chỉ số
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

# In kết quả cuối cùng
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
