#!/usr/bin/env ruby
require 'json'
require 'uri'
require 'net/http'
require 'fileutils'


def pull(endpoint)
    file = File.read('api-credentials.json')
    creds = JSON.parse(file)
    uri = URI("http://14.1.51.192/api/#{endpoint}")
    req = Net::HTTP::Get.new(uri, initheader = {'Content-Type' =>'application/json'})
    req.body = {user: {email: creds['email'], password: creds['password']}}.to_json
    res = Net::HTTP.start(uri.hostname, uri.port) do |http|
        http.request(req)
    end

    FileUtils.mkdir_p('local-json')
    File.open("local-json/#{endpoint}.json", 'w') { |file| file.write(res.body) }
end

pull('customer_users')
pull('products')
pull('vendors')
