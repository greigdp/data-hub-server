#!/usr/bin/ruby
require 'rubygems'

# *** CONFIGURE HERE ***
# Because the fcgi environment is sterile make sure to prepend
# local gem dir to front of gem path.
Gem.path.unshift("/home6/sederunt/ruby/gems")

# Load gems
require 'sinatra'
require 'data_mapper'
require 'mysql'
require 'dm-mysql-adapter'
require 'json'

module Rack
  class Request
    def path_info
      @env["REDIRECT_URL"].to_s
    end
    def path_info=(s)
      @env["REDIRECT_URL"] = s.to_s
    end
  end
end

# The app
DataMapper.setup(  :default, {
                   :adapter => 'mysql',
                   :host => 'localhost',
                   :username => 'sederunt_NCPO',
                   :password => 'Arjan1',
                   :database => 'sederunt_NCPO' })

# FIX;ME: User class goes here.

class Movement
  include DataMapper::Resource

  property :id, Serial
  property :oid, Integer
  property :timestamp, Integer
  property :xaxis, Integer
  property :yaxis, Integer
  property :zaxis, Integer
  property :created_at, DateTime
end

class Temperature
  include DataMapper::Resource

  property :id, Serial
  property :oid, Integer
  property :timestamp, Integer
  property :temp1, Integer
  property :temp2, Integer
  property :created_at, DateTime
end

class Location
  include DataMapper::Resource

  property :id, Serial
  property :oid, Integer
  property :timestamp, Integer
  property :provider, Text
  property :latitude, Float
  property :longitude, Float
  property :accuracy, Float
  property :created_at, DateTime
end

DataMapper.finalize.auto_upgrade!

class App < Sinatra::Application
  get '/' do
    @title = 'Home'
    erb :home
  end

  get '/movements' do
     @movements = Movement.last(50)
     @title = 'Movements'
     erb :movements
  end

  get '/movements/latest' do
    if Movement.last.nil?
      @latest_movement = 0
    else
      @latest_movement = Movement.last.id
    end
    erb :latest_movements, :layout => false
  end

  post '/movements/input' do
    movement_data = JSON.parse(request.body.read)

    movement_data.each do |object|
      m = Movement.new
      m.oid        = object["id"]
      m.timestamp  = object["timestamp"]
      m.xaxis      = object["xaxis"]
      m.yaxis      = object["yaxis"]
      m.zaxis      = object["zaxis"]
      m.created_at = Time.now
      m.save
    end
    redirect '/'
  end

  get '/temperatures' do
     @temperatures = Temperature.last(50)
     @title = 'Temperatures'
     erb :temperatures
  end

  get '/temperatures/latest' do
    if Temperature.last.nil?
      @latest_temperature = 0
    else
      @latest_temperature = Temperature.last.id
    end
    erb :latest_temperature, :layout => false
  end

  post '/temperatures/input' do
    temperature_data = JSON.parse(request.body.read)

    temperature_data.each do |object|
      t = Temperature.new
      t.oid        = object["id"]
      t.timestamp  = object["timestamp"]
      t.temp1      = object["temp1"]
      t.temp2      = object["temp2"]
      t.created_at = Time.now
      t.save
    end
    redirect '/'
  end

  get '/locations' do
     @locations = Location.last(50)
     @title = 'Locations'
     erb :locations
  end

  get '/locations/latest' do
    if Location.last.nil?
      @latest_location = 0
    else
      @latest_location = Location.last.id
    end
    erb :latest_location, :layout => false
  end

  post '/locations/input' do
    location_data = JSON.parse(request.body.read)

    location_data.each do |object|
      l = Location.new
      l.oid        = object["id"]
      l.timestamp  = object["timestamp"]
      l.provider   = object["provider"]
      l.latitude   = object["latitude"]
      l.longitude  = object["longitude"]
      l.accuracy   = object["accuracy"]     
      l.created_at = Time.now
      l.save
    end
    redirect '/'
  end
end

builder = Rack::Builder.new do
  use Rack::ShowStatus
  use Rack::ShowExceptions

  map '/' do
    run App.new
  end
end

Rack::Handler::FastCGI.run(builder)

